<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AttachmentController extends Controller
{
    public function store(Request $request, Visit $visit)
    {
        abort_unless(auth()->user()->isDokter() && $visit->status === 'examining', 403);

        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys(Attachment::TYPES))],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $path = $request->file('file')->store('attachments', 'public');

        $visit->attachments()->create([
            'patient_id' => $visit->patient_id,
            'type' => $validated['type'],
            'original_name' => $request->file('file')->getClientOriginalName(),
            'file_path' => $path,
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Lampiran berhasil diunggah.');
    }

    public function destroy(Visit $visit, Attachment $attachment)
    {
        abort_unless(auth()->user()->isDokter() && $visit->status === 'examining', 403);
        abort_unless($attachment->visit_id === $visit->id, 404);

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('success', 'Lampiran berhasil dihapus.');
    }
}
