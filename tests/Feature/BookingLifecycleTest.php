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

class BookingLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_rejects_checkout_before_checkin(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'Agent']);
        Sanctum::actingAs($user);

        $booking = Booking::factory()->create([
            'client_id' => Client::factory()->create(['branch_id' => $branch->id])->id,
            'car_id' => Car::factory()->create(['branch_id' => $branch->id, 'status' => 'available'])->id,
            'branch_id' => $branch->id,
            'pickup_datetime' => now()->addHour(),
            'return_datetime_planned' => now()->addDays(2),
            'status' => 'reserved',
            'pickup_mileage' => null,
        ]);

        $response = $this->postJson("/api/bookings/{$booking->id}/check-out", [
            'return_mileage' => 12000,
            'return_fuel_level' => 'Full',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');

        $this->assertSame('reserved', $booking->fresh()->status);
    }

    public function test_api_rejects_checkin_for_completed_booking(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'Agent']);
        Sanctum::actingAs($user);

        $booking = Booking::factory()->create([
            'client_id' => Client::factory()->create(['branch_id' => $branch->id])->id,
            'car_id' => Car::factory()->create(['branch_id' => $branch->id, 'status' => 'available'])->id,
            'branch_id' => $branch->id,
            'pickup_datetime' => now()->subDays(3),
            'return_datetime_planned' => now()->subDay(),
            'return_datetime_actual' => now()->subDay(),
            'status' => 'completed',
        ]);

        $response = $this->postJson("/api/bookings/{$booking->id}/check-in", [
            'pickup_mileage' => 10000,
            'pickup_fuel_level' => 'Full',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');

        $this->assertSame('completed', $booking->fresh()->status);
    }

    public function test_api_rejects_marking_car_available_while_active_booking_exists(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'Agent']);
        Sanctum::actingAs($user);

        $car = Car::factory()->create(['branch_id' => $branch->id, 'status' => 'rented']);

        Booking::factory()->create([
            'client_id' => Client::factory()->create(['branch_id' => $branch->id])->id,
            'car_id' => $car->id,
            'branch_id' => $branch->id,
            'pickup_datetime' => now()->subHour(),
            'return_datetime_planned' => now()->addDay(),
            'status' => 'active',
        ]);

        $response = $this->putJson("/api/cars/{$car->id}/status", [
            'status' => 'available',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');

        $this->assertSame('rented', $car->fresh()->status);
    }

    public function test_checkout_keeps_car_reserved_when_future_booking_exists(): void
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'Agent']);
        Sanctum::actingAs($user);

        $car = Car::factory()->create(['branch_id' => $branch->id, 'status' => 'rented']);
        $client = Client::factory()->create(['branch_id' => $branch->id]);

        $activeBooking = Booking::factory()->create([
            'client_id' => $client->id,
            'car_id' => $car->id,
            'branch_id' => $branch->id,
            'pickup_datetime' => now()->subDay(),
            'return_datetime_planned' => now()->addHour(),
            'status' => 'active',
            'pickup_mileage' => 10000,
        ]);

        Booking::factory()->create([
            'client_id' => $client->id,
            'car_id' => $car->id,
            'branch_id' => $branch->id,
            'pickup_datetime' => now()->addDays(3),
            'return_datetime_planned' => now()->addDays(5),
            'status' => 'reserved',
        ]);

        $response = $this->postJson("/api/bookings/{$activeBooking->id}/check-out", [
            'return_mileage' => 10200,
            'return_fuel_level' => 'Full',
        ]);

        $response->assertOk();

        $this->assertSame('completed', $activeBooking->fresh()->status);
        $this->assertSame('reserved', $car->fresh()->status);
    }
}
