<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'full_name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'national_id_number' => $this->faker->numerify('###########'),
            'driver_license_number' => $this->faker->bothify('DL-########'),
            'driver_license_expiry' => $this->faker->dateTimeBetween('now', '+5 years'),
            'address' => $this->faker->address(),
            'date_of_birth' => $this->faker->dateTimeBetween('-60 years', '-18 years'),
            'is_blacklisted' => $this->faker->boolean(5),
            'notes' => $this->faker->optional(0.2)->sentence(),
        ];
    }
}
