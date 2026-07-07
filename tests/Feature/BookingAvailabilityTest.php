<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Car;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_booking_request_rejects_overlapping_reserved_booking(): void
    {
        $branch = Branch::factory()->create();
        User::factory()->create(['branch_id' => $branch->id]);
        $car = Car::factory()->create([
            'branch_id' => $branch->id,
            'status' => 'available',
            'daily_rate' => 8000,
        ]);
        $client = Client::factory()->create(['branch_id' => $branch->id]);

        Booking::factory()->create([
            'client_id' => $client->id,
            'car_id' => $car->id,
            'branch_id' => $branch->id,
            'pickup_datetime' => now()->addDays(3)->setTime(10, 0),
            'return_datetime_planned' => now()->addDays(6)->setTime(10, 0),
            'status' => 'reserved',
        ]);

        $response = $this->from(route('public.car', $car))
            ->post(route('public.book', $car), [
                'full_name' => 'Ouss Test',
                'email' => 'ouss@example.com',
                'phone' => '+213555555555',
                'pickup_datetime' => now()->addDays(4)->setTime(10, 0)->format('Y-m-d\TH:i'),
                'return_datetime' => now()->addDays(5)->setTime(10, 0)->format('Y-m-d\TH:i'),
            ]);

        $response
            ->assertRedirect(route('public.car', $car))
            ->assertSessionHasErrors('pickup_datetime');

        $this->assertSame(1, Booking::where('car_id', $car->id)->count());
    }

    public function test_api_booking_creation_rejects_overlapping_reserved_booking(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'role' => 'agent',
        ]);
        Sanctum::actingAs($user);

        $car = Car::factory()->create([
            'branch_id' => $branch->id,
            'status' => 'available',
            'daily_rate' => 8000,
        ]);
        $existingClient = Client::factory()->create(['branch_id' => $branch->id]);
        $newClient = Client::factory()->create(['branch_id' => $branch->id]);

        Booking::factory()->create([
            'client_id' => $existingClient->id,
            'car_id' => $car->id,
            'branch_id' => $branch->id,
            'pickup_datetime' => now()->addDays(3)->setTime(10, 0),
            'return_datetime_planned' => now()->addDays(6)->setTime(10, 0),
            'status' => 'reserved',
        ]);

        $response = $this->postJson('/api/bookings', [
            'client_id' => $newClient->id,
            'car_id' => $car->id,
            'pickup_datetime' => now()->addDays(4)->setTime(10, 0)->toDateTimeString(),
            'return_datetime_planned' => now()->addDays(5)->setTime(10, 0)->toDateTimeString(),
            'daily_rate_agreed' => 8000,
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('car_id');

        $this->assertSame(1, Booking::where('car_id', $car->id)->count());
    }

    public function test_fleet_date_filter_hides_cars_booked_for_that_period(): void
    {
        $branch = Branch::factory()->create();
        $bookedCar = Car::factory()->create([
            'branch_id' => $branch->id,
            'status' => 'available',
            'brand' => 'Toyota',
            'model' => 'BusyCar',
        ]);
        $availableCar = Car::factory()->create([
            'branch_id' => $branch->id,
            'status' => 'available',
            'brand' => 'Renault',
            'model' => 'FreeCar',
        ]);
        $client = Client::factory()->create(['branch_id' => $branch->id]);

        Booking::factory()->create([
            'client_id' => $client->id,
            'car_id' => $bookedCar->id,
            'branch_id' => $branch->id,
            'pickup_datetime' => now()->addDays(3)->setTime(10, 0),
            'return_datetime_planned' => now()->addDays(6)->setTime(10, 0),
            'status' => 'reserved',
        ]);

        $response = $this->get(route('public.fleet', [
            'pickup_datetime' => now()->addDays(4)->setTime(10, 0)->format('Y-m-d\TH:i'),
            'return_datetime' => now()->addDays(5)->setTime(10, 0)->format('Y-m-d\TH:i'),
        ]));

        $response
            ->assertOk()
            ->assertDontSee($bookedCar->model)
            ->assertSee($availableCar->model);
    }

    public function test_api_car_list_can_filter_by_available_period(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'role' => 'Agent',
        ]);
        Sanctum::actingAs($user);

        $bookedCar = Car::factory()->create([
            'branch_id' => $branch->id,
            'status' => 'available',
            'brand' => 'Toyota',
            'model' => 'ApiBusyCar',
        ]);
        $availableCar = Car::factory()->create([
            'branch_id' => $branch->id,
            'status' => 'available',
            'brand' => 'Renault',
            'model' => 'ApiFreeCar',
        ]);
        $client = Client::factory()->create(['branch_id' => $branch->id]);

        Booking::factory()->create([
            'client_id' => $client->id,
            'car_id' => $bookedCar->id,
            'branch_id' => $branch->id,
            'pickup_datetime' => now()->addDays(3)->setTime(10, 0),
            'return_datetime_planned' => now()->addDays(6)->setTime(10, 0),
            'status' => 'reserved',
        ]);

        $response = $this->getJson('/api/cars?' . http_build_query([
            'pickup_datetime' => now()->addDays(4)->setTime(10, 0)->toDateTimeString(),
            'return_datetime' => now()->addDays(5)->setTime(10, 0)->toDateTimeString(),
        ]));

        $response
            ->assertOk()
            ->assertJsonMissing(['id' => $bookedCar->id])
            ->assertJsonFragment(['id' => $availableCar->id]);
    }
}
