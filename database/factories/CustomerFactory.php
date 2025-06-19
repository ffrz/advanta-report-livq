<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    protected $businessTypes = [
        "Retail",
        "Elektronik",
        "Konstruksi",
        "Jasa",
        "Perdagangan",
        "Pertanian",
        "Perikanan",
        "Peternakan",
        "Toko Bangunan",
        "Restoran",
        "Toko Kelontong",
        "Toko Kosmetik",
        "Toko Baju",
        "Toko Sepatu",
    ];

    protected $sources = [
        "Website",
        "Social Media",
        "Referral",
        "Event",
        "Walk-in",
        "Other",
    ];
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assigned_user_id' => User::inRandomOrder()->value('id'),
            'name' => $this->faker->name(),
            'company' => $this->faker->company(),
            'business_type' => $this->faker->randomElement($this->businessTypes),
            'source' => $this->faker->randomElement($this->sources),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'created_datetime' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
