<?php

namespace App\Http\Controllers;

use App\Models\DisplaySetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DisplaySettingController extends Controller
{
    public function edit()
    {
        $setting = DisplaySetting::current();

        return view('queues.display-settings', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'announcement_template' => ['required', 'string', 'max:500'],
            'chime_style' => ['required', Rule::in(array_keys(DisplaySetting::CHIME_STYLES))],
            'voice_name' => ['nullable', 'string', 'max:255'],
            'voice_lang' => ['required', 'string', 'max:20'],
            'voice_rate' => ['required', 'numeric', 'min:0.5', 'max:2'],
            'voice_pitch' => ['required', 'numeric', 'min:0', 'max:2'],
        ]);

        DisplaySetting::current()->update($validated);

        return back()->with('success', 'Pengaturan layar antrean berhasil disimpan.');
    }
}
