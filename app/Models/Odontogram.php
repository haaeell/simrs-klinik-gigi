<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'patient_id', 'visit_id', 'doctor_id', 'examination_date',
    'occlusion', 'torus_palatinus', 'torus_mandibularis', 'palate', 'diastema', 'dental_anomaly', 'notes',
])]
class Odontogram extends Model
{
    public const UPPER_TEETH = ['18', '17', '16', '15', '14', '13', '12', '11', '21', '22', '23', '24', '25', '26', '27', '28'];

    public const LOWER_TEETH = ['48', '47', '46', '45', '44', '43', '42', '41', '31', '32', '33', '34', '35', '36', '37', '38'];

    public const CONDITIONS = [
        'Normal', 'Karies', 'Tambalan', 'Gigi Hilang', 'Perawatan Saluran Akar',
        'Mahkota / Crown', 'Gigi Patah', 'Sisa Akar', 'Belum Erupsi', 'Lainnya',
        'Non Vital', 'Fraktur', 'Un-erupted', 'Partial Erupted', 'Anomali', 'Implant', 'Crown',
    ];

    public const SURFACES = ['M', 'O', 'D', 'V', 'L'];

    protected function casts(): array
    {
        return [
            'examination_date' => 'date',
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

    public function teeth(): HasMany
    {
        return $this->hasMany(OdontogramTooth::class);
    }
}
