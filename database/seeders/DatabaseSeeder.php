<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Car;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\CarMaintenanceLog;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Roles
        $superAdminRole = Role::create(['name' => 'Super Admin']);
        $managerRole = Role::create(['name' => 'Manager']);
        $agentRole = Role::create(['name' => 'Agent']);

        // 2. Create Branches
        $branches = Branch::factory(3)->create();

        // 3. Create Staff Users (15)
        $superAdmin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'Super Admin',
            'branch_id' => $branches->first()->id,
        ]);
        $superAdmin->assignRole($superAdminRole);

        $users = User::factory(14)->create(function () use ($branches) {
            return [
                'branch_id' => $branches->random()->id,
                'role' => fake()->randomElement(['Manager', 'Agent']),
                'phone' => fake()->phoneNumber(),
            ];
        });

        foreach ($users as $user) {
            $user->assignRole($user->role);
        }

        // 4. Create Clients (60)
        $clients = Client::factory(60)->create();

        // 5. Create Cars (40)
        $cars = Car::factory(40)->create(function () use ($branches) {
            return ['branch_id' => $branches->random()->id];
        });

        // Add some maintenance logs
        foreach ($cars as $car) {
            if (fake()->boolean(30)) {
                CarMaintenanceLog::factory(fake()->numberBetween(1, 3))->create([
                    'car_id' => $car->id,
                ]);
            }
        }

        // 6. Create Bookings (150)
        $staffIds = User::pluck('id')->toArray();
        $clientIds = Client::pluck('id')->toArray();

        $bookings = Booking::factory(150)->make()->each(function ($booking) use ($staffIds, $clientIds, $cars) {
            $car = $cars->random();
            $booking->car_id = $car->id;
            $booking->client_id = fake()->randomElement($clientIds);
            $booking->agent_id = fake()->randomElement($staffIds);
            $booking->branch_id = $car->branch_id;
            $booking->save();

            // Create payment for booking
            Payment::factory()->create([
                'booking_id' => $booking->id,
                'recorded_by' => $booking->agent_id,
            ]);
        });
        
        // 7. Initial Settings
        \App\Models\Setting::create([
            'company_name' => 'Antigravity Car Rental',
            'currency' => 'DZD',
            'tax_rate' => 19.00,
        ]);
    }
}
