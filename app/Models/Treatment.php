<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Treatment extends Model
{
    protected $fillable = ['visit_id', 'tooth_number', 'diagnosis', 'icd10_code', 'treatment', 'notes'];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }
}
