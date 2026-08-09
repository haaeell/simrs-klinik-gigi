<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\Queue;
use Illuminate\Database\Seeder;

class QueueSeeder extends Seeder
{
    public function run(): void
    {
        $today = now()->toDateString();
        $patients = Patient::inRandomOrder()->limit(8)->get();

        // 'examining' and 'done' are left for VisitSeeder to pair with a matching Visit record.
        $statuses = ['waiting', 'waiting', 'called', 'waiting', 'skipped', 'waiting', 'examining', 'done'];

        foreach ($patients as $i => $patient) {
            $status = $statuses[$i] ?? 'waiting';

            Queue::create([
                'patient_id' => $patient->id,
                'queue_number' => 'A-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'queue_date' => $today,
                'status' => $status,
                'called_at' => in_array($status, ['called', 'examining', 'done'], true) ? now()->subMinutes(rand(20, 40)) : null,
                'started_at' => in_array($status, ['examining', 'done'], true) ? now()->subMinutes(rand(5, 19)) : null,
                'finished_at' => $status === 'done' ? now()->subMinutes(rand(1, 4)) : null,
            ]);
        }
    }
}
