<?php

namespace Database\Factories;

use App\Models\Car;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarMaintenanceLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'car_id' => Car::factory(),
            'type' => $this->faker->randomElement(['service', 'repair', 'accident', 'inspection']),
            'description' => $this->faker->paragraph(),
            'cost' => $this->faker->randomFloat(2, 50, 2000),
            'date' => $this->faker->date(),
            'performed_by' => $this->faker->company(),
            'odometer' => $this->faker->numberBetween(5000, 150000),
        ];
    }
}
