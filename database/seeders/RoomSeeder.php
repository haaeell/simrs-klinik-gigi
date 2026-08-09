<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Sample weekly schedules per room index, so the seeded clinic demonstrates doctors
     * practicing on different days (0 = Minggu ... 6 = Sabtu, matches Carbon::dayOfWeek).
     */
    private array $scheduleTemplates = [
        [1 => ['08:00', '16:00'], 2 => ['08:00', '16:00'], 3 => ['08:00', '16:00'], 4 => ['08:00', '16:00'], 5 => ['08:00', '16:00']],
        [2 => ['09:00', '15:00'], 4 => ['09:00', '15:00'], 6 => ['09:00', '15:00']],
    ];

    public function run(): void
    {
        $doctors = User::where('role', 'dokter')->orderBy('id')->get();

        foreach ($doctors as $i => $doctor) {
            $room = Room::create([
                'name' => 'Ruang '.($i + 1),
                'doctor_id' => $doctor->id,
                'is_active' => true,
            ]);

            foreach ($this->scheduleTemplates[$i] ?? [] as $day => [$start, $end]) {
                $room->schedules()->create([
                    'day_of_week' => $day,
                    'start_time' => $start,
                    'end_time' => $end,
                ]);
            }
        }
    }
}
