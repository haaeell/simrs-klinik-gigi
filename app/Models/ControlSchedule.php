<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

#[Fillable(['patient_id', 'visit_id', 'doctor_id', 'control_date', 'notes', 'status'])]
class ControlSchedule extends Model
{
    public const STATUSES = ['scheduled' => 'Terjadwal', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'];

    protected function casts(): array
    {
        return [
            'control_date' => 'date',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => self::STATUSES[$this->status] ?? $this->status);
    }

    protected function statusBadgeClass(): Attribute
    {
        return Attribute::get(fn () => match ($this->status) {
            'completed' => 'bg-emerald-50 text-emerald-700',
            'cancelled' => 'bg-slate-100 text-slate-500',
            default => 'bg-amber-50 text-amber-700',
        });
    }

    /**
     * "Kontrol besok" / "kontrol hari ini" / "kontrol terlewat" — internal reminder text,
     * built once here so the dashboard, notification bell, and Jadwal Kontrol page reuse it.
     */
    protected function reminderText(): Attribute
    {
        return Attribute::get(function () {
            if ($this->status !== 'scheduled') {
                return null;
            }

            $today = now()->toDateString();
            $controlDate = $this->control_date->toDateString();

            return match (true) {
                $controlDate === $today => 'Jadwal kontrol Anda hari ini.',
                $controlDate === now()->addDay()->toDateString() => 'Jadwal kontrol Anda besok.',
                $controlDate < $today => 'Jadwal kontrol Anda sudah lewat.',
                default => null,
            };
        });
    }

    /**
     * Control-schedule reminders due for a patient (besok/hari ini/terlewat), built once here
     * so the patient dashboard, notification bell, and Jadwal Kontrol page all reuse it.
     */
    public static function remindersFor(int $patientId): Collection
    {
        return self::where('patient_id', $patientId)
            ->where('status', 'scheduled')
            ->whereDate('control_date', '<=', now()->addDay())
            ->orderBy('control_date')
            ->get()
            ->map(fn (self $schedule) => ['text' => $schedule->reminder_text, 'schedule' => $schedule])
            ->filter(fn (array $item) => $item['text'])
            ->values();
    }
}
