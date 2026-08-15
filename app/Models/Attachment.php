<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Attachment extends Model
{
    protected $fillable = ['patient_id', 'visit_id', 'type', 'original_name', 'file_path', 'description'];

    public const TYPES = [
        'xray' => 'X-Ray',
        'photo' => 'Foto',
        'laboratory' => 'Laboratorium',
        'informed_consent' => 'Informed Consent',
        'informed_refusal' => 'Informed Refusal',
        'other' => 'Lainnya',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    protected function typeLabel(): Attribute
    {
        return Attribute::get(fn () => self::TYPES[$this->type] ?? $this->type);
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn () => Storage::disk('public')->url($this->file_path));
    }

    protected function isImage(): Attribute
    {
        return Attribute::get(fn () => in_array(Str::lower(pathinfo($this->original_name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'], true));
    }
}
