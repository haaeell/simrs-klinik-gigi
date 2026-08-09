<?php

namespace App\Http\Controllers;

use App\Models\Treatment;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        $visits = Visit::with(['patient', 'doctor'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->whereHas('patient', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('medical_record_number', 'like', "%{$search}%");
                });
            })
            ->latest('visit_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('visits.index', compact('visits'));
    }

    public function show(Visit $visit)
    {
        $visit->load(['patient.medicalHistory', 'doctor', 'treatments', 'odontogram.teeth', 'attachments', 'controlSchedule']);

        $canEdit = auth()->user()->isDokter() && $visit->status === 'examining';

        return view('visits.show', compact('visit', 'canEdit'));
    }

    public function update(Request $request, Visit $visit)
    {
        abort_unless(auth()->user()->isDokter() && $visit->status === 'examining', 403);

        $validated = $request->validate([
            'complaint' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string', 'max:255'],
            'icd10_code' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
            'needs_control' => ['nullable', 'boolean'],
            'control_date' => ['required_if:needs_control,1', 'nullable', 'date', 'after_or_equal:today'],
            'control_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $visit->update([
            'complaint' => $validated['complaint'] ?? null,
            'diagnosis' => $validated['diagnosis'] ?? null,
            'icd10_code' => $validated['icd10_code'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        if (! empty($validated['needs_control'])) {
            $visit->controlSchedule()->updateOrCreate([], [
                'patient_id' => $visit->patient_id,
                'doctor_id' => auth()->id(),
                'control_date' => $validated['control_date'],
                'notes' => $validated['control_notes'] ?? null,
                'status' => 'scheduled',
            ]);
        } else {
            $visit->controlSchedule()->delete();
        }

        return back()->with('success', 'Data pemeriksaan berhasil disimpan.');
    }

    public function complete(Visit $visit)
    {
        abort_unless(auth()->user()->isDokter() && $visit->status === 'examining', 403);

        DB::transaction(function () use ($visit) {
            $visit->update(['status' => 'completed']);

            if ($visit->queue_id) {
                $visit->queue()->update(['status' => 'done', 'finished_at' => now()]);
            }
        });

        return redirect()->route('queues.index')->with('success', 'Pemeriksaan berhasil diselesaikan.');
    }

    public function storeTreatment(Request $request, Visit $visit)
    {
        abort_unless(auth()->user()->isDokter() && $visit->status === 'examining', 403);

        $validated = $request->validate([
            'tooth_number' => ['nullable', 'string', 'max:20'],
            'diagnosis' => ['nullable', 'string', 'max:255'],
            'icd10_code' => ['nullable', 'string', 'max:20'],
            'treatment' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $visit->treatments()->create($validated);

        return back()->with('success', 'Tindakan berhasil ditambahkan.');
    }

    public function destroyTreatment(Visit $visit, Treatment $treatment)
    {
        abort_unless(auth()->user()->isDokter() && $visit->status === 'examining', 403);
        abort_unless($treatment->visit_id === $visit->id, 404);

        $treatment->delete();

        return back()->with('success', 'Tindakan berhasil dihapus.');
    }
}
