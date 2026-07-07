<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Car;
use App\Support\BranchAccess;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date');

        $bookings = BranchAccess::scope(Booking::with(['client', 'car']), user: $request->user())
            ->when($date, function (Builder $query, string $date): void {
                $query->where(function (Builder $query) use ($date): void {
                    $query
                        ->whereDate('pickup_datetime', $date)
                        ->orWhereDate('return_datetime_planned', $date);
                });
            })
            ->latest()
            ->paginate(20);

        return response()->json($bookings);
    }

    public function show(Request $request, $id)
    {
        $booking = BranchAccess::scope(
            Booking::with(['client', 'car', 'payments', 'conditionPhotos']),
            user: $request->user(),
        )->findOrFail($id);

        return response()->json($booking);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'car_id' => 'required|exists:cars,id',
            'pickup_datetime' => 'required|date',
            'return_datetime_planned' => 'required|date|after:pickup_datetime',
            'daily_rate_agreed' => 'required|numeric',
            'deposit_amount' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
        ]);

        $car = BranchAccess::scope(Car::query(), user: $request->user())->findOrFail($validated['car_id']);

        $pickup = Carbon::parse($validated['pickup_datetime']);
        $return = Carbon::parse($validated['return_datetime_planned']);
        if (in_array($car->status, ['rented', 'maintenance', 'out_of_service'], true)) {
            throw ValidationException::withMessages([
                'car_id' => __('This car is not available for booking.'),
            ]);
        }

        if (Booking::hasConflict($car->id, $pickup, $return)) {
            throw ValidationException::withMessages([
                'car_id' => __('This car is already booked during the selected dates.'),
            ]);
        }

        $days = max((int) ceil($pickup->diffInHours($return) / 24), 1);
        $totalAmount = ($days * (float) $validated['daily_rate_agreed']) - (float) ($validated['discount'] ?? 0);

        $booking = Booking::create(array_merge($validated, [
            'reference_number' => 'BKG-' . strtoupper(Str::random(8)),
            'agent_id' => $request->user()->id,
            'branch_id' => $car->branch_id,
            'total_amount' => $totalAmount,
            'status' => 'reserved',
        ]));

        $car->refreshOperationalStatus();

        return response()->json(['message' => __('Booking created'), 'booking' => $booking], 201);
    }

    public function checkIn(Request $request, $id)
    {
        $booking = BranchAccess::scope(Booking::query(), user: $request->user())->findOrFail($id);

        if ($booking->status !== 'reserved') {
            throw ValidationException::withMessages([
                'status' => __('Only reserved bookings can be checked in.'),
            ]);
        }

        $request->validate([
            'pickup_mileage' => 'required|integer',
            'pickup_fuel_level' => 'required|string',
            'photos.*' => 'image|max:5120',
        ]);

        $booking->update([
            'status' => 'active',
            'pickup_mileage' => $request->pickup_mileage,
            'pickup_fuel_level' => $request->pickup_fuel_level,
        ]);

        $booking->car->update(['status' => 'rented', 'mileage' => $request->pickup_mileage]);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('bookings/conditions', 'public');
                $booking->conditionPhotos()->create([
                    'type' => 'pickup',
                    'photo_path' => $path,
                ]);
            }
        }

        return response()->json(['message' => __('Check-in completed successfully'), 'booking' => $booking]);
    }

    public function checkOut(Request $request, $id)
    {
        $booking = BranchAccess::scope(Booking::query(), user: $request->user())->findOrFail($id);

        if (! in_array($booking->status, ['active', 'overdue'], true)) {
            throw ValidationException::withMessages([
                'status' => __('Only active or overdue bookings can be checked out.'),
            ]);
        }

        $request->validate([
            'return_mileage' => 'required|integer|gte:' . $booking->pickup_mileage,
            'return_fuel_level' => 'required|string',
            'photos.*' => 'image|max:5120',
        ]);

        $booking->update([
            'status' => 'completed',
            'return_datetime_actual' => now(),
            'return_mileage' => $request->return_mileage,
            'return_fuel_level' => $request->return_fuel_level,
        ]);

        $booking->car->refreshOperationalStatus((int) $request->return_mileage);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('bookings/conditions', 'public');
                $booking->conditionPhotos()->create([
                    'type' => 'return',
                    'photo_path' => $path,
                ]);
            }
        }

        return response()->json(['message' => __('Check-out completed successfully'), 'booking' => $booking]);
    }
}
