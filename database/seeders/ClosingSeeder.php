<?php

namespace Database\Seeders;

use App\Models\Closing;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClosingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 97; $i++) {
            Closing::create([
                'user_id' => User::inRandomOrder()->value('id'),
                'customer_id' => Customer::inRandomOrder()->value('id'),
                'service_id' =>  Service::inRandomOrder()->value('id'),
                'date' => fake()->dateTimeBetween('-1 year', 'now'),
                'description' => fake()->word(),
                'amount' => fake()->randomNumber(2) * 250000,
            ]);
        }
    }
}
