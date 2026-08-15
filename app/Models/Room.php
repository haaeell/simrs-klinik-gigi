<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = ['name', 'doctor_id', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(RoomSchedule::class);
    }

    public static function active()
    {
        return self::where('is_active', true)->whereNotNull('doctor_id')->with('doctor')->orderBy('name')->get();
    }

    /**
     * Active rooms with their schedules preloaded — used everywhere a booking form needs to
     * know, per room, whether the doctor actually practices today (see todaySchedule()/
     * isOpenNow() below). Built once here so staff, online, and QR booking forms stay in sync.
     */
    public static function activeWithSchedules()
    {
        return self::where('is_active', true)->whereNotNull('doctor_id')->with(['doctor', 'schedules'])->orderBy('name')->get();
    }

    /**
     * This room's schedule row for today, if the doctor practices today at all.
     */
    public function todaySchedule(): ?RoomSchedule
    {
        return $this->schedules
            ->firstWhere('day_of_week', now()->dayOfWeek);
    }

    public function isOpenNow(): bool
    {
        $schedule = $this->todaySchedule();

        if (! $schedule) {
            return false;
        }

        $now = now()->format('H:i:s');

        return $now >= $schedule->start_time->format('H:i:s') && $now <= $schedule->end_time->format('H:i:s');
    }

    /**
     * Short label for booking dropdowns, e.g. "08:00–16:00" or "Tidak praktik hari ini".
     */
    public function todayStatusLabel(): string
    {
        $schedule = $this->todaySchedule();

        if (! $schedule) {
            return 'Tidak praktik hari ini';
        }

        return $schedule->start_time->format('H:i').'–'.$schedule->end_time->format('H:i');
    }
}
