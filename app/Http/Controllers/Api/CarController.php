<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Car;
use App\Support\BranchAccess;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CarController extends Controller
{
    public function index(Request $request)
    {
        $cars = BranchAccess::scope(Car::with('photos'), user: $request->user())
            ->when($request->search, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('plate_number', 'like', "%{$search}%");
                });
            })
            ->when($request->status, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when(
                $request->filled(['pickup_datetime', 'return_datetime']),
                fn (Builder $query): Builder => $query->availableForPeriod(
                    Carbon::parse($request->pickup_datetime),
                    Carbon::parse($request->return_datetime),
                ),
            )
            ->get();

        return response()->json($cars);
    }

    public function show(Request $request, $id)
    {
        $car = BranchAccess::scope(
            Car::with([
                'photos',
                'maintenanceLogs',
                'statusHistories',
                'bookings' => fn (Builder $query) => $query->latest()->take(5)->with('client'),
            ]),
            user: $request->user(),
        )->findOrFail($id);

        return response()->json($car);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:available,rented,reserved,maintenance,out_of_service',
            'notes' => 'nullable|string',
        ]);

        $car = BranchAccess::scope(Car::query(), user: $request->user())->findOrFail($id);

        if (
            $request->status === 'available'
            && Booking::query()
                ->where('car_id', $car->id)
                ->whereIn('status', ['active', 'overdue'])
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'status' => __('This car has an active booking and cannot be marked available.'),
            ]);
        }

        if ($car->status !== $request->status) {
            $car->status = $request->status;
            $car->save();

            $car->statusHistories()->create([
                'status' => $request->status,
                'changed_at' => now(),
                'changed_by' => $request->user()->id,
                'notes' => $request->notes,
            ]);

            \App\Models\ActivityLog::create([
                'action' => 'updated_car_status',
                'subject_type' => Car::class,
                'subject_id' => $car->id,
                'causer_id' => $request->user()->id,
                'properties' => ['new_status' => $request->status, 'notes' => $request->notes],
            ]);
        }

        return response()->json(['message' => __('Status updated successfully.'), 'car' => $car]);
    }
}
