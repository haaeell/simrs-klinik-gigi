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
        $visit->load(['patient.medicalHistory', 'doctor', 'treatments', 'odontogram.teeth', 'attachments']);

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
        ]);

        $visit->update($validated);

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
