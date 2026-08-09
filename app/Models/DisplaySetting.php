<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'announcement_template', 'chime_style', 'voice_name', 'voice_lang', 'voice_rate', 'voice_pitch',
])]
class DisplaySetting extends Model
{
    public const CHIME_STYLES = [
        'ding-dong' => 'Ding-Dong',
        'bell' => 'Lonceng Tunggal',
        'double-beep' => 'Beep Ganda',
    ];

    protected function casts(): array
    {
        return [
            'voice_rate' => 'float',
            'voice_pitch' => 'float',
        ];
    }

    /**
     * Single settings row for the queue TV display — created on first access with sane
     * defaults, so both the admin settings page and the public display API can share it.
     */
    public static function current(): self
    {
        return self::firstOrCreate(['id' => 1], [
            'announcement_template' => 'Nomor antrian {letter}, {number}. Atas nama {patient_name}. Silakan menuju ruang pemeriksaan.',
        ]);
    }

    /**
     * Replace {letter}/{number}/{patient_name} placeholders with real values for TTS.
     */
    public function renderAnnouncement(string $queueNumber, string $patientName): string
    {
        $letter = preg_replace('/[^A-Za-z]/', '', $queueNumber);
        $number = (int) preg_replace('/\D/', '', $queueNumber);

        return strtr($this->announcement_template, [
            '{letter}' => $letter,
            '{number}' => (string) $number,
            '{patient_name}' => $patientName,
        ]);
    }
}
