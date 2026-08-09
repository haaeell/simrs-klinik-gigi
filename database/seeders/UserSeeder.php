<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Klinik',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'drg. Ahmad',
            'email' => 'doctor@example.com',
            'password' => 'password',
            'role' => 'dokter',
        ]);

        User::create([
            'name' => 'drg. Siti Rahma',
            'email' => 'doctor2@example.com',
            'password' => 'password',
            'role' => 'dokter',
        ]);
    }
}
