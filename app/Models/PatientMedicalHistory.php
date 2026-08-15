<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientMedicalHistory extends Model
{
    protected $fillable = [
        'patient_id', 'blood_type', 'systolic', 'diastolic',
        'heart_disease', 'diabetes', 'haemophilia', 'hepatitis',
        'other_disease', 'drug_allergy', 'food_allergy', 'notes',
    ];

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
