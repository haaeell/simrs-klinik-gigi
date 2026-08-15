<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SystemSetting extends Model
{
    protected $fillable = ['clinic_name', 'address', 'phone', 'email', 'logo_path', 'primary_color'];

    /**
     * Single settings row for clinic branding — memoized per request so the layout,
     * login page, TV display, and print headers can all call this without repeating
     * the query on every include.
     */
    public static function current(): self
    {
        static $instance;

        return $instance ??= self::firstOrCreate(['id' => 1], ['clinic_name' => 'Klinik Gigi']);
    }

    protected function logoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null);
    }
}
