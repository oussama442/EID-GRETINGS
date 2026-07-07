<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\Client;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    public function definition(): array
    {
        $pickup = $this->faker->dateTimeBetween('-1 year', '+1 month');
        $return_planned = (clone $pickup)->modify('+' . $this->faker->numberBetween(1, 14) . ' days');
        
        $status = $this->faker->randomElement(['reserved', 'active', 'completed', 'cancelled', 'overdue']);
        $return_actual = $status === 'completed' ? (clone $return_planned)->modify($this->faker->numberBetween(-1, 2) . ' days') : null;

        $daily_rate = $this->faker->randomFloat(2, 30, 200);
        $days = $pickup->diff($return_planned)->days ?: 1;
        
        return [
            'reference_number' => strtoupper($this->faker->unique()->bothify('BKG-####-????')),
            'client_id' => Client::factory(),
            'car_id' => Car::factory(),
            'agent_id' => User::factory(),
            'branch_id' => Branch::factory(),
            'pickup_datetime' => $pickup,
            'return_datetime_planned' => $return_planned,
            'return_datetime_actual' => $return_actual,
            'pickup_location' => 'Main Office',
            'return_location' => 'Main Office',
            'daily_rate_agreed' => $daily_rate,
            'total_amount' => $daily_rate * $days,
            'deposit_amount' => $this->faker->randomElement([0, 100, 200, 500]),
            'status' => $status,
            'pickup_mileage' => $this->faker->numberBetween(1000, 50000),
            'return_mileage' => $status === 'completed' ? $this->faker->numberBetween(50100, 51000) : null,
            'pickup_fuel_level' => $this->faker->randomElement(['1/4', '1/2', '3/4', 'Full']),
            'return_fuel_level' => $status === 'completed' ? $this->faker->randomElement(['Empty', '1/4', '1/2', 'Full']) : null,
        ];
    }
}
