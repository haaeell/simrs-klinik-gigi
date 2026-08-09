<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'patient_id', 'queue_number', 'queue_date', 'status', 'registration_source',
    'called_at', 'started_at', 'finished_at',
])]
class Queue extends Model
{
    /**
     * A queue still "in play" today — blocks the same patient from getting a second number.
     */
    public const ACTIVE_STATUSES = ['waiting', 'called', 'examining'];

    public const SOURCES = [
        'online' => 'Online',
        'qr' => 'QR',
        'staff' => 'Petugas',
    ];

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

    public static function hasActiveToday(int $patientId): bool
    {
        return self::where('patient_id', $patientId)
            ->where('queue_date', now()->toDateString())
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->exists();
    }

    public static function activeToday(int $patientId): ?self
    {
        return self::with('patient')
            ->where('patient_id', $patientId)
            ->where('queue_date', now()->toDateString())
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->first();
    }

    /**
     * Create the next sequential A-001 style number for today (reused by staff, online, and QR booking).
     * Wrapped in a transaction + row lock to stay safe under concurrent requests.
     */
    public static function createForPatient(int $patientId, string $source = 'staff'): self
    {
        $today = now()->toDateString();

        return DB::transaction(function () use ($patientId, $today, $source) {
            $last = self::where('queue_date', $today)->lockForUpdate()->orderByDesc('id')->first();
            $next = $last ? ((int) substr($last->queue_number, 2)) + 1 : 1;

            return self::create([
                'patient_id' => $patientId,
                'queue_number' => 'A-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT),
                'queue_date' => $today,
                'status' => 'waiting',
                'registration_source' => $source,
            ]);
        });
    }

    /**
     * How many active queues today have a lower number than this one — i.e. still ahead in line.
     */
    public function patientsAhead(): int
    {
        return self::where('queue_date', $this->queue_date)
            ->where('queue_number', '<', $this->queue_number)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->count();
    }

    /**
     * Average examination duration (started_at -> finished_at) from the last 20 completed queues.
     * Falls back to 15 minutes when there isn't enough history yet. No ML — just a rolling average,
     * built once here and reused everywhere an estimate is needed.
     */
    public static function averageExaminationMinutes(): int
    {
        $minutes = self::where('status', 'done')
            ->whereNotNull('started_at')
            ->whereNotNull('finished_at')
            ->latest('finished_at')
            ->limit(20)
            ->get(['started_at', 'finished_at'])
            ->map(fn ($queue) => $queue->started_at->diffInMinutes($queue->finished_at))
            ->filter(fn ($minutes) => $minutes > 0);

        return $minutes->isEmpty() ? 15 : (int) round($minutes->avg());
    }

    /**
     * Average time from registration to being called, for today's queues that have been
     * called. Used only for the admin monitoring dashboard (separate from the per-patient
     * wait estimate above, which projects forward instead of looking back).
     */
    public static function averageWaitMinutesToday(): int
    {
        $minutes = self::where('queue_date', now()->toDateString())
            ->whereNotNull('called_at')
            ->get(['created_at', 'called_at'])
            ->map(fn ($queue) => $queue->created_at->diffInMinutes($queue->called_at))
            ->filter(fn ($minutes) => $minutes >= 0);

        return $minutes->isEmpty() ? 0 : (int) round($minutes->avg());
    }

    public function estimatedWaitMinutes(): int
    {
        return $this->patientsAhead() * self::averageExaminationMinutes();
    }

    /**
     * Queue-proximity reminder text for a patient's active queue today, if any — built once here
     * so the dashboard, notification bell, and /patient/queue polling all use the same wording.
     */
    public static function reminderTextFor(int $patientId): ?string
    {
        $queue = self::activeToday($patientId);

        return match (true) {
            $queue === null => null,
            $queue->status === 'called' => 'NOMOR ANDA SEDANG DIPANGGIL.',
            $queue->status === 'waiting' && $queue->patientsAhead() <= 2 => 'Antrean Anda sudah dekat. Silakan bersiap.',
            default => null,
        };
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

    protected function sourceLabel(): Attribute
    {
        return Attribute::get(fn () => self::SOURCES[$this->registration_source] ?? $this->registration_source);
    }

    protected function sourceBadgeClass(): Attribute
    {
        return Attribute::get(fn () => match ($this->registration_source) {
            'online' => 'bg-blue-50 text-blue-700',
            'qr' => 'bg-violet-50 text-violet-700',
            'staff' => 'bg-slate-100 text-slate-600',
            default => 'bg-slate-100 text-slate-600',
        });
    }
}
