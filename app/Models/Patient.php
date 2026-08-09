<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'medical_record_number', 'nik', 'name', 'birth_place', 'birth_date',
    'gender', 'occupation', 'address', 'phone',
])]
class Patient extends Model
{
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    public function medicalHistory(): HasOne
    {
        return $this->hasOne(PatientMedicalHistory::class);
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function latestVisit(): HasOne
    {
        return $this->hasOne(Visit::class)->latestOfMany('visit_date');
    }

    public function odontograms(): HasMany
    {
        return $this->hasMany(Odontogram::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    protected function genderLabel(): Attribute
    {
        return Attribute::get(fn () => match ($this->gender) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => '-',
        });
    }
}
