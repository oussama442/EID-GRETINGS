<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarFactory extends Factory
{
    public function definition(): array
    {
        $daily_rate = $this->faker->randomFloat(2, 30, 200);
        return [
            'brand' => $this->faker->randomElement(['Toyota', 'Renault', 'Peugeot', 'Hyundai', 'Kia', 'Volkswagen']),
            'model' => $this->faker->word(),
            'year' => $this->faker->numberBetween(2018, 2024),
            'plate_number' => $this->faker->unique()->bothify('??-####-??'),
            'vin' => $this->faker->unique()->bothify('WVGZZZ############'),
            'category' => $this->faker->randomElement(['Economy', 'Compact', 'SUV', 'Luxury']),
            'color' => $this->faker->colorName(),
            'transmission' => $this->faker->randomElement(['Manual', 'Automatic']),
            'fuel_type' => $this->faker->randomElement(['Petrol', 'Diesel', 'Hybrid']),
            'seats' => $this->faker->randomElement([4, 5, 7]),
            'daily_rate' => $daily_rate,
            'weekly_rate' => $daily_rate * 6,
            'monthly_rate' => $daily_rate * 20,
            'mileage' => $this->faker->numberBetween(1000, 150000),
            'status' => $this->faker->randomElement(['available', 'rented', 'reserved', 'maintenance']),
            'branch_id' => Branch::factory(),
            'features' => json_encode(['Bluetooth', 'AC', 'GPS']),
            'insurance_expiry' => $this->faker->dateTimeBetween('now', '+1 year'),
            'registration_expiry' => $this->faker->dateTimeBetween('now', '+1 year'),
            'last_service_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'next_service_due' => $this->faker->dateTimeBetween('now', '+6 months'),
        ];
    }
}
