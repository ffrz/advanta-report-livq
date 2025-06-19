<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            'General',
            'CCTV',
            'Software',
            'POS',
            'Computer',
            'GPS',
            'Networking',
            'Printer',
            'Server',
            'Other',
        ];

        foreach ($services as $serviceName) {
            Service::create([
                'name' => $serviceName,
                'active' => 1,
                'notes' => fake()->optional()->paragraph(3),
            ]);
        }
    }
}
