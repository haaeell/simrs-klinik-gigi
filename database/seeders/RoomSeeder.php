<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = User::where('role', 'dokter')->orderBy('id')->get();

        foreach ($doctors as $i => $doctor) {
            Room::create([
                'name' => 'Ruang '.($i + 1),
                'doctor_id' => $doctor->id,
                'is_active' => true,
            ]);
        }
    }
}
