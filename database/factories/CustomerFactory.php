<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    protected $type = [
        "Distributor",
        "R1",
        "R2",
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $address = $this->faker->address();
        return [
            'assigned_user_id' => null,
            'name' => $this->faker->company(),
            'type' => $this->faker->randomElement(['Distributor', 'R1', 'R2']),
            'phone' => $this->faker->phoneNumber(),
            'address' => $address,
            'shipping_address' => $address,
        ];
    }
}
