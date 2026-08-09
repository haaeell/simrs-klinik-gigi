<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'patient_id', 'blood_type', 'systolic', 'diastolic',
    'heart_disease', 'diabetes', 'haemophilia', 'hepatitis',
    'other_disease', 'drug_allergy', 'food_allergy', 'notes',
])]
class PatientMedicalHistory extends Model
{
    protected function casts(): array
    {
        return [
            'heart_disease' => 'boolean',
            'diabetes' => 'boolean',
            'haemophilia' => 'boolean',
            'hepatitis' => 'boolean',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
