<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Car;
use App\Support\BranchAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();

        $query = BranchAccess::scope(
            Booking::with(['client', 'car']),
            user: $request->user(),
        );

        $pickups = (clone $query)->whereDate('pickup_datetime', $today)
            ->whereIn('status', ['reserved', 'active'])
            ->get();

        $returns = (clone $query)->whereDate('return_datetime_planned', $today)
            ->whereIn('status', ['active', 'overdue'])
            ->get();

        $overdueReturns = (clone $query)
            ->whereIn('status', ['active', 'overdue'])
            ->where('return_datetime_planned', '<', now())
            ->orderBy('return_datetime_planned')
            ->get();

        $carStatus = BranchAccess::scope(Car::query(), user: $request->user())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'pickups_today' => $pickups,
            'returns_today' => $returns,
            'overdue_returns' => $overdueReturns,
            'pickups_count' => $pickups->count(),
            'returns_count' => $returns->count(),
            'overdue_count' => $overdueReturns->count(),
            'car_status' => [
                'available' => (int) ($carStatus['available'] ?? 0),
                'reserved' => (int) ($carStatus['reserved'] ?? 0),
                'rented' => (int) ($carStatus['rented'] ?? 0),
                'maintenance' => (int) ($carStatus['maintenance'] ?? 0),
                'out_of_service' => (int) ($carStatus['out_of_service'] ?? 0),
            ],
        ]);
    }
}
