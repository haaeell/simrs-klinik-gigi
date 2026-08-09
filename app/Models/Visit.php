<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'patient_id', 'queue_id', 'doctor_id', 'visit_date',
    'complaint', 'diagnosis', 'icd10_code', 'notes', 'status',
])]
class Visit extends Model
{
    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class);
    }

    public function odontogram(): HasOne
    {
        return $this->hasOne(Odontogram::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => match ($this->status) {
            'examining' => 'Sedang Diperiksa',
            'completed' => 'Selesai',
            default => $this->status,
        });
    }

    protected function statusBadgeClass(): Attribute
    {
        return Attribute::get(fn () => match ($this->status) {
            'examining' => 'bg-violet-50 text-violet-700',
            'completed' => 'bg-emerald-50 text-emerald-700',
            default => 'bg-slate-100 text-slate-600',
        });
    }
}
