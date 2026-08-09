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

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function controlSchedules(): HasMany
    {
        return $this->hasMany(ControlSchedule::class);
    }

    /**
     * Next upcoming (not-yet-passed) scheduled control, if any — used by the patient
     * dashboard and reminder surfaces.
     */
    public function nextControlSchedule(): HasOne
    {
        return $this->hasOne(ControlSchedule::class)
            ->where('status', 'scheduled')
            ->oldestOfMany('control_date');
    }

    protected function genderLabel(): Attribute
    {
        return Attribute::get(fn () => match ($this->gender) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => '-',
        });
    }

    /**
     * Next sequential RM-000001 style number. Must be called inside a DB transaction
     * (uses lockForUpdate) to stay safe under concurrent patient registration.
     */
    public static function generateNextMedicalRecordNumber(): string
    {
        $last = self::lockForUpdate()->orderByDesc('id')->first();
        $next = $last ? ((int) substr($last->medical_record_number, 3)) + 1 : 1;

        return 'RM-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
