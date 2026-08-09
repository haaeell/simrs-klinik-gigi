<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['patient_id', 'queue_number', 'queue_date', 'status', 'called_at', 'started_at', 'finished_at'])]
class Queue extends Model
{
    protected function casts(): array
    {
        return [
            'queue_date' => 'date',
            'called_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): HasOne
    {
        return $this->hasOne(Visit::class);
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => match ($this->status) {
            'waiting' => 'Menunggu',
            'called' => 'Dipanggil',
            'examining' => 'Sedang Diperiksa',
            'skipped' => 'Dilewati',
            'done' => 'Selesai',
            default => $this->status,
        });
    }

    protected function statusBadgeClass(): Attribute
    {
        return Attribute::get(fn () => match ($this->status) {
            'waiting' => 'bg-amber-50 text-amber-700',
            'called' => 'bg-blue-50 text-blue-700',
            'examining' => 'bg-violet-50 text-violet-700',
            'skipped' => 'bg-slate-100 text-slate-600',
            'done' => 'bg-emerald-50 text-emerald-700',
            default => 'bg-slate-100 text-slate-600',
        });
    }
}
