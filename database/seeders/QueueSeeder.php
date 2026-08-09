<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\Queue;
use App\Models\Room;
use Illuminate\Database\Seeder;

class QueueSeeder extends Seeder
{
    /**
     * Short complaints staff would jot down while booking the queue (see queues/create.blade.php).
     */
    private array $complaints = [
        'Gigi sakit ketika mengunyah',
        'Gusi bengkak dan berdarah',
        'Ingin cabut gigi bungsu',
        'Kontrol rutin',
        'Gigi berlubang dan ngilu',
        'Gigi goyang',
        'Ingin pasang kawat gigi',
        'Nyeri gigi geraham belakang',
    ];

    public function run(): void
    {
        $today = now()->toDateString();
        $patients = Patient::inRandomOrder()->limit(8)->get();
        $rooms = Room::all();

        // 'examining' and 'done' are left for VisitSeeder to pair with a matching Visit record.
        $statuses = ['waiting', 'waiting', 'called', 'waiting', 'skipped', 'waiting', 'examining', 'done'];

        foreach ($patients as $i => $patient) {
            $status = $statuses[$i] ?? 'waiting';

            // Most queues get a room/doctor picked at booking time (the normal staff flow).
            // Index 3 is left without one on purpose, to also demo the call-time room picker
            // that still applies to online/QR self check-ins.
            $room = ($rooms->isNotEmpty() && $i !== 3) ? $rooms[$i % $rooms->count()] : null;

            // Build a chronologically consistent timeline (registered -> called -> started ->
            // finished), each step strictly after the previous one, so wait/duration reports
            // never end up with a negative "waktu tunggu" like an earlier version of this seeder did.
            $createdAt = now()->subMinutes(rand(45, 90));
            $calledAt = in_array($status, ['called', 'examining', 'done'], true) ? $createdAt->copy()->addMinutes(rand(5, 20)) : null;
            $startedAt = in_array($status, ['examining', 'done'], true) ? $calledAt->copy()->addMinutes(rand(1, 5)) : null;
            $finishedAt = $status === 'done' ? $startedAt->copy()->addMinutes(rand(10, 25)) : null;

            $queue = Queue::create([
                'patient_id' => $patient->id,
                'queue_number' => 'A-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'queue_date' => $today,
                'status' => $status,
                'room_id' => $room?->id,
                'complaint' => $this->complaints[$i] ?? null,
                'called_at' => $calledAt,
                'started_at' => $startedAt,
                'finished_at' => $finishedAt,
            ]);

            $queue->timestamps = false;
            $queue->forceFill(['created_at' => $createdAt, 'updated_at' => $finishedAt ?? $startedAt ?? $calledAt ?? $createdAt])->save();
        }
    }
}
