<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Interaction;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class InteractionFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->value('id'),
            'customer_id' => Customer::inRandomOrder()->value('id'),
            'service_id' =>  Service::inRandomOrder()->value('id'),
            'date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'type' => $this->faker->randomElement(array_keys(Interaction::Types)),
            'status' => Interaction::Status_Done,
            'engagement_level' => $this->faker->randomElement(array_keys(Interaction::EngagementLevels)),
            'subject' => $this->faker->word(),
            'summary' => $this->faker->sentence(1),
            'notes' => $this->faker->sentence(2),
            'created_datetime' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
