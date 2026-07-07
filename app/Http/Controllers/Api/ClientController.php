<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Support\BranchAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = $this->scopedClients($request)
            ->when($request->search, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('national_id_number', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20);

        return response()->json($clients);
    }

    public function show(Request $request, $id)
    {
        $client = $this->scopedClients($request)
            ->with(['bookings' => function (Builder $query) use ($request): void {
                BranchAccess::scope($query, user: $request->user())
                    ->latest()
                    ->with('car')
                    ->take(10);
            }])
            ->findOrFail($id);

        return response()->json($client);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'national_id_number' => 'nullable|string|max:50',
            'driver_license_number' => 'nullable|string|max:50',
            'driver_license_expiry' => 'nullable|date',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'id_document_photo' => 'nullable|image|max:5120',
            'license_photo' => 'nullable|image|max:5120',
        ]);

        if (BranchAccess::isRestricted($request->user())) {
            $validated['branch_id'] = BranchAccess::branchId($request->user());
        }

        if ($request->hasFile('id_document_photo')) {
            $validated['id_document_photo'] = $request->file('id_document_photo')->store('clients/id_documents', 'public');
        }

        if ($request->hasFile('license_photo')) {
            $validated['license_photo'] = $request->file('license_photo')->store('clients/licenses', 'public');
        }

        $client = Client::create($validated);

        return response()->json(['message' => __('Client created successfully'), 'client' => $client], 201);
    }

    private function scopedClients(Request $request): Builder
    {
        $query = Client::query();

        if (BranchAccess::isRestricted($request->user())) {
            $query->where(function (Builder $query) use ($request): void {
                $query
                    ->where('branch_id', BranchAccess::branchId($request->user()))
                    ->orWhereHas('bookings', fn (Builder $query): Builder => BranchAccess::scope($query, user: $request->user()));
            });
        }

        return $query;
    }
}
