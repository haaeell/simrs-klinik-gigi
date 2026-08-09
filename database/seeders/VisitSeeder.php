<?php

namespace Database\Seeders;

use App\Models\Odontogram;
use App\Models\Patient;
use App\Models\Queue;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class VisitSeeder extends Seeder
{
    /**
     * Small pool of realistic dental cases to randomize across seeded visits.
     */
    private array $cases = [
        ['complaint' => 'Gigi sakit ketika mengunyah', 'diagnosis' => 'Dental Caries', 'icd10' => 'K02.9', 'treatment' => 'Penambalan gigi', 'tooth' => '16'],
        ['complaint' => 'Gusi bengkak dan berdarah', 'diagnosis' => 'Gingivitis Kronis', 'icd10' => 'K05.1', 'treatment' => 'Scaling', 'tooth' => null],
        ['complaint' => 'Gigi berlubang besar dan nyeri', 'diagnosis' => 'Pulpitis Akut', 'icd10' => 'K04.0', 'treatment' => 'Perawatan saluran akar', 'tooth' => '26'],
        ['complaint' => 'Ingin cabut gigi bungsu', 'diagnosis' => 'Impaksi Molar Tiga', 'icd10' => 'K01.1', 'treatment' => 'Pencabutan gigi', 'tooth' => '38'],
        ['complaint' => 'Kontrol rutin', 'diagnosis' => 'Dental Check Up', 'icd10' => 'Z01.2', 'treatment' => 'Pembersihan karang gigi', 'tooth' => null],
        ['complaint' => 'Gigi goyang', 'diagnosis' => 'Periodontitis', 'icd10' => 'K05.3', 'treatment' => 'Splinting gigi', 'tooth' => '41'],
    ];

    /**
     * Tooth condition/surface combos used to seed odontogram data.
     */
    private array $toothConditions = [
        ['condition' => 'Karies', 'surfaces' => ['O']],
        ['condition' => 'Karies', 'surfaces' => ['M', 'O', 'D']],
        ['condition' => 'Gigi Hilang', 'surfaces' => []],
        ['condition' => 'Sisa Akar', 'surfaces' => []],
        ['condition' => 'Non Vital', 'surfaces' => []],
        ['condition' => 'Crown', 'surfaces' => []],
    ];

    public function run(): void
    {
        $doctorIds = User::where('role', 'dokter')->pluck('id');

        // Pair today's 'examining'/'done' queues (from QueueSeeder) with a matching visit.
        // 'examining' is left without an odontogram to exercise the lazy-create-on-first-save path.
        $this->createFromTodayQueue('examining', 'examining', $doctorIds, addTreatment: false, addOdontogram: false);
        $this->createFromTodayQueue('done', 'completed', $doctorIds, addTreatment: true, addOdontogram: true);

        // Historical completed visits so Riwayat Perawatan / Rekam Medis / Odontogram / Laporan have real data to browse and filter.
        $patients = Patient::inRandomOrder()->limit(12)->get();

        foreach ($patients as $patient) {
            $case = fake()->randomElement($this->cases);

            $visit = Visit::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctorIds->random(),
                'visit_date' => now()->subDays(rand(1, 45))->toDateString(),
                'complaint' => $case['complaint'],
                'diagnosis' => $case['diagnosis'],
                'icd10_code' => $case['icd10'],
                'notes' => fake()->boolean(30) ? 'Kontrol kembali 1 minggu.' : null,
                'status' => 'completed',
            ]);

            $visit->treatments()->create([
                'tooth_number' => $case['tooth'],
                'diagnosis' => $case['diagnosis'],
                'icd10_code' => $case['icd10'],
                'treatment' => $case['treatment'],
            ]);

            $this->seedOdontogram($visit);
        }
    }

    private function createFromTodayQueue(string $queueStatus, string $visitStatus, Collection $doctorIds, bool $addTreatment, bool $addOdontogram): void
    {
        $queue = Queue::where('queue_date', now()->toDateString())->where('status', $queueStatus)->first();

        if (! $queue) {
            return;
        }

        $case = fake()->randomElement($this->cases);

        $visit = Visit::create([
            'patient_id' => $queue->patient_id,
            'queue_id' => $queue->id,
            // Mirrors QueueController::startExamination(): the doctor is whoever the queue's
            // room belongs to, falling back to a random one for room-less (online/QR) queues.
            'doctor_id' => $queue->room?->doctor_id ?? $doctorIds->random(),
            'visit_date' => now()->toDateString(),
            'complaint' => $queue->complaint ?? $case['complaint'],
            'diagnosis' => $visitStatus === 'completed' ? $case['diagnosis'] : null,
            'icd10_code' => $visitStatus === 'completed' ? $case['icd10'] : null,
            'status' => $visitStatus,
        ]);

        if ($addTreatment) {
            $visit->treatments()->create([
                'tooth_number' => $case['tooth'],
                'diagnosis' => $case['diagnosis'],
                'icd10_code' => $case['icd10'],
                'treatment' => $case['treatment'],
            ]);
        }

        if ($addOdontogram) {
            $this->seedOdontogram($visit);
        }
    }

    private function seedOdontogram(Visit $visit): void
    {
        $odontogram = Odontogram::create([
            'patient_id' => $visit->patient_id,
            'visit_id' => $visit->id,
            'doctor_id' => $visit->doctor_id,
            'examination_date' => $visit->visit_date,
            'occlusion' => fake()->randomElement(['Normal', 'Cross Bite', 'Deep Bite']),
        ]);

        $teeth = fake()->randomElements([...Odontogram::UPPER_TEETH, ...Odontogram::LOWER_TEETH], rand(2, 4));

        foreach ($teeth as $toothNumber) {
            $combo = fake()->randomElement($this->toothConditions);

            $odontogram->teeth()->create([
                'tooth_number' => $toothNumber,
                'condition' => $combo['condition'],
                'surfaces' => implode('', $combo['surfaces']),
            ]);
        }
    }
}
