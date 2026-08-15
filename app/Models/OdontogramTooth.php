<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdontogramTooth extends Model
{
    protected $fillable = ['odontogram_id', 'tooth_number', 'condition', 'surfaces', 'notes'];

    public function odontogram(): BelongsTo
    {
        return $this->belongsTo(Odontogram::class);
    }
}
