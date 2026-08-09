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
     * Replace {letter}/{number}/{patient_name}/{room} placeholders with real values for TTS.
     *
     * If a room is given but the admin's template doesn't reference {room} at all, the room
     * is still announced by appending a short sentence — otherwise a clinic with rooms set up
     * would silently never hear where to send the patient just because the template predates
     * that feature (or was customized before rooms existed).
     */
    public function renderAnnouncement(string $queueNumber, string $patientName, ?string $roomName = null): string
    {
        $letter = preg_replace('/[^A-Za-z]/', '', $queueNumber);
        $number = (int) preg_replace('/\D/', '', $queueNumber);
        $template = $this->announcement_template;
        $mentionsRoom = str_contains($template, '{room}');

        $text = strtr($template, [
            '{letter}' => $letter,
            '{number}' => (string) $number,
            '{patient_name}' => $patientName,
            '{room}' => $roomName ?? '',
        ]);

        if ($roomName && ! $mentionsRoom) {
            $text .= " Silakan menuju {$roomName}.";
        }

        return $text;
    }
}
