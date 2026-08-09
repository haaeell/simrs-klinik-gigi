<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'doctor_id', 'is_active'])]
class Room extends Model
{
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

    public static function active()
    {
        return self::where('is_active', true)->whereNotNull('doctor_id')->with('doctor')->orderBy('name')->get();
    }
}
