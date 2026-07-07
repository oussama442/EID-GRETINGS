<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'amount' => $this->faker->randomFloat(2, 50, 1000),
            'method' => $this->faker->randomElement(['cash', 'card', 'transfer']),
            'type' => $this->faker->randomElement(['deposit', 'partial', 'full']),
            'paid_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'recorded_by' => User::factory(),
            'receipt_number' => $this->faker->unique()->bothify('REC-########'),
        ];
    }
}
