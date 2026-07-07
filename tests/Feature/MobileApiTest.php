<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Car;
use App\Models\Client;
use App\Models\Booking;
use App\Models\Branch;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_api_flow()
    {
        $this->seed();

        $user = User::where('email', 'admin@example.com')->first();
        
        // 1. Test Login
        $response = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        
        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'user']);

        $token = $response->json('token');
        
        // 2. Test Dashboard
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->getJson('/api/dashboard');
        
        $response->assertStatus(200)
                 ->assertJsonStructure(['pickups_today', 'returns_today', 'pickups_count', 'returns_count']);

        // 3. Test Cars List & Status Update
        $car = Car::first();
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->getJson('/api/cars');
        $response->assertStatus(200);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->putJson('/api/cars/' . $car->id . '/status', [
                             'status' => 'maintenance',
                             'notes' => 'Test maintenance'
                         ]);
        $response->assertStatus(200);
        $this->assertEquals('maintenance', $car->fresh()->status);

        $bookingCar = Car::where('status', 'available')->whereKeyNot($car->id)->first() ?: Car::factory()->create([
            'status' => 'available',
            'branch_id' => $user->branch_id,
        ]);

        // 4. Test Client Creation
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/clients', [
                             'full_name' => 'John Doe',
                             'phone' => '+1234567890',
                         ]);
        $response->assertStatus(201);
        $clientId = $response->json('client.id');

        // 5. Test Booking Creation
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/bookings', [
                             'client_id' => $clientId,
                             'car_id' => $bookingCar->id,
                             'pickup_datetime' => now()->addDay()->toDateTimeString(),
                             'return_datetime_planned' => now()->addDays(3)->toDateTimeString(),
                             'daily_rate_agreed' => 50,
                         ]);
        $response->assertStatus(201);
        $bookingId = $response->json('booking.id');

        // 6. Test Check-in
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson("/api/bookings/{$bookingId}/check-in", [
                             'pickup_mileage' => 10000,
                             'pickup_fuel_level' => 'Full',
                         ]);
        $response->assertStatus(200);
        
        // 7. Test Check-out
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson("/api/bookings/{$bookingId}/check-out", [
                             'return_mileage' => 10100,
                             'return_fuel_level' => '1/2',
                         ]);
        $response->assertStatus(200);
        $this->assertEquals('completed', Booking::find($bookingId)->status);
    }
}
