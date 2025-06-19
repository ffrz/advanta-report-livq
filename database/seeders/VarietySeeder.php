<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Variety;
use Database\Factories\UserFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VarietySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $variety = [
            "Madu 59 F1",
            "Anara 81 F1",
            "Reva F1",
            "Lilac F1",
            "Nona 23 F1",
            "Beijing F1",
            "Deby 23 F1",
            "Gogor F1",
            "Lavanta F1",
            "Herra 22",
        ];

        foreach ($variety as $name) {
            Variety::create([
                'name' => $name,
                'active' => 1,
                'notes' => fake()->optional()->paragraph(3),
            ]);
        }
    }
}
