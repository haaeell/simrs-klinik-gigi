<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $patients = Patient::query()
            ->with('latestVisit')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('medical_record_number', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('patients.index', compact('patients'));
    }

    public function create()
    {
        return view('patients.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatePatient($request);

        $patient = DB::transaction(function () use ($validated) {
            $validated['medical_record_number'] = $this->generateMedicalRecordNumber();

            return Patient::create($validated);
        });

        return redirect()->route('patients.show', $patient)->with('success', 'Pasien baru berhasil ditambahkan.');
    }

    public function show(Request $request, Patient $patient)
    {
        $patient->load([
            'medicalHistory',
            'visits' => fn ($query) => $query->with(['doctor', 'treatments'])->latest('visit_date')->latest('id'),
            'odontograms' => fn ($query) => $query->with(['visit', 'doctor', 'teeth'])->latest('examination_date')->latest('id'),
            'attachments' => fn ($query) => $query->latest(),
        ]);

        $selectedOdontogram = $patient->odontograms->firstWhere('id', (int) $request->query('odontogram_id'))
            ?? $patient->odontograms->first();

        return view('patients.show', compact('patient', 'selectedOdontogram'));
    }

    public function edit(Patient $patient)
    {
        return view('patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient)
    {
        $validated = $this->validatePatient($request, $patient);

        $patient->update($validated);

        return redirect()->route('patients.show', $patient)->with('success', 'Data pasien berhasil diperbarui.');
    }

    public function updateMedicalHistory(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'blood_type' => ['nullable', 'string', 'max:5'],
            'systolic' => ['nullable', 'integer', 'min:0', 'max:400'],
            'diastolic' => ['nullable', 'integer', 'min:0', 'max:400'],
            'heart_disease' => ['sometimes', 'boolean'],
            'diabetes' => ['sometimes', 'boolean'],
            'haemophilia' => ['sometimes', 'boolean'],
            'hepatitis' => ['sometimes', 'boolean'],
            'other_disease' => ['nullable', 'string', 'max:255'],
            'drug_allergy' => ['nullable', 'string', 'max:255'],
            'food_allergy' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        foreach (['heart_disease', 'diabetes', 'haemophilia', 'hepatitis'] as $flag) {
            $validated[$flag] = $request->boolean($flag);
        }

        $patient->medicalHistory()->updateOrCreate(['patient_id' => $patient->id], $validated);

        return redirect()->route('patients.show', ['patient' => $patient, 'tab' => 'medis'])
            ->with('success', 'Data medis pasien berhasil disimpan.');
    }

    private function validatePatient(Request $request, ?Patient $patient = null): array
    {
        return $request->validate([
            'nik' => ['nullable', 'digits:16', Rule::unique('patients', 'nik')->ignore($patient?->id)],
            'name' => ['required', 'string', 'max:255'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'gender' => ['nullable', Rule::in(['L', 'P'])],
            'occupation' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);
    }

    private function generateMedicalRecordNumber(): string
    {
        $last = Patient::lockForUpdate()->orderByDesc('id')->first();
        $next = $last ? ((int) substr($last->medical_record_number, 3)) + 1 : 1;

        return 'RM-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
