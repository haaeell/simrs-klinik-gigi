<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PatientRegistrationController extends Controller
{
    public function choose()
    {
        return view('register-patient.choose');
    }

    public function showExisting(Request $request)
    {
        $patient = $this->verifiedPatient($request);

        return view('register-patient.existing', compact('patient'));
    }

    public function verifyExisting(Request $request)
    {
        $validated = $request->validate([
            'nik' => ['required', 'digits:16'],
            'phone' => ['required', 'string', 'max:20'],
        ], [], ['nik' => 'NIK', 'phone' => 'No. HP']);

        $patient = Patient::where('nik', $validated['nik'])->first();
        $inputPhone = preg_replace('/\D+/', '', $validated['phone']);
        $storedPhone = $patient ? preg_replace('/\D+/', '', (string) $patient->phone) : '';

        if (! $patient || $storedPhone === '' || $storedPhone !== $inputPhone) {
            return back()->withInput()->with('error', 'Data pasien tidak ditemukan atau tidak sesuai. Silakan hubungi petugas klinik.');
        }

        if ($patient->user()->exists()) {
            return back()->withInput()->with('error', 'Pasien ini sudah memiliki akun. Silakan masuk (login).');
        }

        $request->session()->put('register_verified_patient_id', $patient->id);

        return redirect()->route('register-patient.existing');
    }

    public function cancelExisting(Request $request)
    {
        $request->session()->forget('register_verified_patient_id');

        return redirect()->route('register-patient.existing');
    }

    public function storeExisting(Request $request)
    {
        $patient = $this->verifiedPatient($request);

        if (! $patient) {
            return redirect()->route('register-patient.existing')->with('error', 'Sesi verifikasi sudah berakhir. Silakan ulangi.');
        }

        if ($patient->user()->exists()) {
            return redirect()->route('register-patient.choose')->with('error', 'Pasien ini sudah memiliki akun. Silakan masuk (login).');
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [], ['email' => 'email', 'password' => 'kata sandi']);

        $user = DB::transaction(fn () => User::create([
            'name' => $patient->name,
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'pasien',
            'patient_id' => $patient->id,
        ]));

        $request->session()->forget('register_verified_patient_id');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('patient.dashboard')->with('success', 'Akun berhasil dibuat. Selamat datang, '.$patient->name.'.');
    }

    public function showNew()
    {
        return view('register-patient.new');
    }

    public function storeNew(Request $request)
    {
        $validated = $request->validate([
            'nik' => ['nullable', 'digits:16', Rule::unique('patients', 'nik')],
            'name' => ['required', 'string', 'max:255'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'gender' => ['nullable', Rule::in(['L', 'P'])],
            'occupation' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $patient = Patient::create([
                'medical_record_number' => Patient::generateNextMedicalRecordNumber(),
                'nik' => $validated['nik'] ?? null,
                'name' => $validated['name'],
                'birth_place' => $validated['birth_place'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'occupation' => $validated['occupation'] ?? null,
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
            ]);

            return User::create([
                'name' => $patient->name,
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'pasien',
                'patient_id' => $patient->id,
            ]);
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('patient.dashboard')->with('success', 'Pendaftaran berhasil. Selamat datang, '.$user->name.'.');
    }

    private function verifiedPatient(Request $request): ?Patient
    {
        $patientId = $request->session()->get('register_verified_patient_id');

        return $patientId ? Patient::find($patientId) : null;
    }
}
