<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'id' => 1,
                'name' => 'CHRISTIEN YUNIANTO',
                'role' => User::Role_ASM,
                'work_area' => null,
                'parent_id' => null,
            ],
            [
                'id' => 2,
                'name' => 'AGUS HERDIANTO',
                'role' => User::Role_Agronomist,
                'work_area' => null,
                'parent_id' => 1,
            ],
            [
                'id' => 3,
                'name' => 'AGUS NURJANI',
                'role' => User::Role_Agronomist,
                'work_area' => null,
                'parent_id' => 1,
            ],
            [
                'id' => 4,
                'name' => 'CHANDRA',
                'role' => User::Role_Agronomist,
                'work_area' => null,
                'parent_id' => 1,
            ],
            [
                'id' => 5,
                'name' => 'FATKHROKMAN',
                'role' => User::Role_BS,
                'work_area' => 'CIREBON',
                'parent_id' => 2,
            ],
            [
                'id' => 6,
                'name' => 'IING MUBAROK',
                'role' => User::Role_BS,
                'work_area' => 'KUNINGAN - MJL HL',
                'parent_id' => 2,
            ],
            [
                'id' => 7,
                'name' => 'RIFKI FAISAL',
                'role' => User::Role_BS,
                'work_area' => 'INDRAMAYU - MJL LL',
                'parent_id' => 2,
            ],
            [
                'id' => 8,
                'name' => 'LISTIANTO',
                'role' => User::Role_BS,
                'work_area' => 'SUBANG - PURWAKARTA',
                'parent_id' => 2,
            ],
        ];

        $password = Hash::make('12345');

        foreach ($users as &$user) {
            $username = strtolower(str_replace(' ', '', $user['name']));
            $user['name'] = ucwords(strtolower($user['name']));
            $user['username'] = $username;
            $user['password'] = $password;
            $user['work_area'] = ucwords(strtolower($user['work_area']));
            $user['active'] = true;
        }

        DB::table('users')->insert($users);
        DB::table('users')->insert([
            [
                'username' => 'admin',
                'password' => $password,
                'name' => 'Admin',
                'active' => true,
                'role' => User::Role_Admin,
            ]
        ]);
    }
}
