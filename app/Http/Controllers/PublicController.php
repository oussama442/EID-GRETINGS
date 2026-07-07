<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Car;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PublicController extends Controller
{
    public function home()
    {
        $featuredCars = Car::where('status', 'available')->with('photos')->inRandomOrder()->take(6)->get();

        return view('public.home', compact('featuredCars'));
    }

    public function fleet(Request $request)
    {
        $query = Car::where('status', 'available')->with('photos');
        $filters = [
            'categories' => Car::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
            'transmissions' => Car::query()->whereNotNull('transmission')->distinct()->orderBy('transmission')->pluck('transmission'),
            'fuelTypes' => Car::query()->whereNotNull('fuel_type')->distinct()->orderBy('fuel_type')->pluck('fuel_type'),
        ];

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('transmission')) {
            $query->where('transmission', $request->transmission);
        }

        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }

        if ($request->filled('seats')) {
            $query->where('seats', '>=', (int) $request->seats);
        }

        if ($request->filled('max_daily_rate')) {
            $query->where('daily_rate', '<=', (float) $request->max_daily_rate);
        }

        if ($request->filled('search')) {
            $query->where(function ($query) use ($request): void {
                $query
                    ->where('brand', 'like', '%' . $request->search . '%')
                    ->orWhere('model', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled(['pickup_datetime', 'return_datetime'])) {
            $request->validate([
                'pickup_datetime' => 'date|after:today',
                'return_datetime' => 'date|after:pickup_datetime',
            ]);

            $query->availableForPeriod(
                Carbon::parse($request->pickup_datetime),
                Carbon::parse($request->return_datetime),
            );
        }

        $cars = $query->orderBy('daily_rate')->paginate(12)->withQueryString();

        return view('public.fleet', compact('cars', 'filters'));
    }

    public function showCar($id)
    {
        $car = Car::where('status', 'available')->with('photos', 'branch')->findOrFail($id);

        return view('public.car_details', compact('car'));
    }

    public function requestBooking(Request $request, $id)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'pickup_datetime' => 'required|date|after:today',
            'return_datetime' => 'required|date|after:pickup_datetime',
        ]);

        $car = Car::where('status', 'available')->with('branch')->findOrFail($id);
        $pickup = Carbon::parse($request->pickup_datetime);
        $return = Carbon::parse($request->return_datetime);

        if (Booking::hasConflict($car->id, $pickup, $return)) {
            throw ValidationException::withMessages([
                'pickup_datetime' => __('This car is already booked during the selected dates. Please choose another period.'),
            ]);
        }

        $client = Client::firstOrCreate(
            ['email' => $request->email],
            [
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'branch_id' => $car->branch_id,
            ],
        );

        if (! $client->branch_id) {
            $client->update(['branch_id' => $car->branch_id]);
        }

        $days = max((int) ceil($pickup->diffInHours($return) / 24), 1);

        Booking::create([
            'reference_number' => 'REQ-' . strtoupper(Str::random(8)),
            'client_id' => $client->id,
            'car_id' => $car->id,
            'branch_id' => $car->branch_id,
            'agent_id' => \App\Models\User::query()->first()?->id,
            'pickup_datetime' => $pickup,
            'return_datetime_planned' => $return,
            'daily_rate_agreed' => $car->daily_rate,
            'total_amount' => $car->daily_rate * $days,
            'status' => 'reserved',
            'pickup_location' => $car->branch?->name ?: __('Main office'),
            'return_location' => $car->branch?->name ?: __('Main office'),
        ]);

        $car->refreshOperationalStatus();

        return redirect()
            ->route('public.success')
            ->with('success', __('Your booking request was sent successfully.'));
    }
}
