<?php

namespace App\Http\Controllers;

use App\Models\Odontogram;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OdontogramController extends Controller
{
    public function update(Request $request, Visit $visit)
    {
        abort_unless(auth()->user()->isDokter() && $visit->status === 'examining', 403);

        $validated = $request->validate([
            'occlusion' => ['nullable', 'string', 'max:255'],
            'torus_palatinus' => ['nullable', 'string', 'max:255'],
            'torus_mandibularis' => ['nullable', 'string', 'max:255'],
            'palate' => ['nullable', 'string', 'max:255'],
            'diastema' => ['nullable', 'string', 'max:255'],
            'dental_anomaly' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->odontogramFor($visit)->update($validated);

        return back()->with('success', 'Catatan umum odontogram berhasil disimpan.');
    }

    public function updateTooth(Request $request, Visit $visit)
    {
        abort_unless(auth()->user()->isDokter() && $visit->status === 'examining', 403);

        $validated = $request->validate([
            'tooth_number' => ['required', Rule::in([...Odontogram::UPPER_TEETH, ...Odontogram::LOWER_TEETH])],
            'condition' => ['nullable', Rule::in(Odontogram::CONDITIONS)],
            'surfaces' => ['nullable', 'array'],
            'surfaces.*' => [Rule::in(Odontogram::SURFACES)],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $surfaces = collect(Odontogram::SURFACES)
            ->filter(fn ($surface) => in_array($surface, $validated['surfaces'] ?? [], true))
            ->implode('');

        $tooth = $this->odontogramFor($visit)->teeth()->updateOrCreate(
            ['tooth_number' => $validated['tooth_number']],
            [
                'condition' => $validated['condition'] ?? null,
                'surfaces' => $surfaces ?: null,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return response()->json([
            'tooth' => [
                'tooth_number' => $tooth->tooth_number,
                'condition' => $tooth->condition,
                'surfaces' => $tooth->surfaces,
                'notes' => $tooth->notes,
                'has_condition' => ! empty($tooth->condition) && $tooth->condition !== 'Normal',
            ],
        ]);
    }

    private function odontogramFor(Visit $visit): Odontogram
    {
        return $visit->odontogram()->firstOrCreate([], [
            'patient_id' => $visit->patient_id,
            'doctor_id' => $visit->doctor_id,
            'examination_date' => $visit->visit_date,
        ]);
    }
}
