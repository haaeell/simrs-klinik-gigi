<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 15) as $i) {
            $gender = fake()->randomElement(['L', 'P']);

            $patient = Patient::create([
                'medical_record_number' => 'RM-'.str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                'nik' => $i % 4 === 0 ? null : fake()->unique()->numerify('################'),
                'name' => $gender === 'L' ? fake()->name('male') : fake()->name('female'),
                'birth_place' => fake()->city(),
                'birth_date' => fake()->dateTimeBetween('-70 years', '-5 years')->format('Y-m-d'),
                'gender' => $gender,
                'occupation' => fake()->randomElement(['Karyawan Swasta', 'Wiraswasta', 'PNS', 'Pelajar/Mahasiswa', 'Ibu Rumah Tangga', 'Guru']),
                'address' => fake()->address(),
                'phone' => '08'.fake()->numerify('##########'),
            ]);

            if ($i % 2 === 0) {
                $patient->medicalHistory()->create([
                    'blood_type' => fake()->randomElement(['A', 'B', 'AB', 'O']),
                    'systolic' => fake()->numberBetween(100, 140),
                    'diastolic' => fake()->numberBetween(60, 90),
                    'heart_disease' => fake()->boolean(10),
                    'diabetes' => fake()->boolean(15),
                    'haemophilia' => false,
                    'hepatitis' => fake()->boolean(5),
                    'drug_allergy' => fake()->boolean(20) ? 'Amoxicillin' : null,
                    'food_allergy' => fake()->boolean(15) ? 'Seafood' : null,
                ]);
            }
        }
    }
}
