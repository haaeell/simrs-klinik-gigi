<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['odontogram_id', 'tooth_number', 'condition', 'surfaces', 'notes'])]
class OdontogramTooth extends Model
{
    public function odontogram(): BelongsTo
    {
        return $this->belongsTo(Odontogram::class);
    }
}
