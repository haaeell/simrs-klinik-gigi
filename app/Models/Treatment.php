<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['visit_id', 'tooth_number', 'diagnosis', 'icd10_code', 'treatment', 'notes'])]
class Treatment extends Model
{
    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }
}
