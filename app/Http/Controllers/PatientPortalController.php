<?php

namespace App\Http\Controllers;

use App\Models\ControlSchedule;
use App\Models\Odontogram;
use App\Models\Queue;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientPortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $patient = $request->user()->patient()->with(['latestVisit.doctor', 'nextControlSchedule'])->first();
        $activeQueue = Queue::activeToday($patient->id);
        $reminders = $this->remindersFor($patient->id);

        return view('patient.dashboard', compact('patient', 'activeQueue', 'reminders'));
    }

    public function controlSchedules(Request $request)
    {
        $patient = $request->user()->patient;

        $schedules = $patient->controlSchedules()
            ->with('visit')
            ->latest('control_date')
            ->get();

        return view('patient.control-schedules', compact('patient', 'schedules'));
    }

    public function notifications(Request $request)
    {
        $patient = $request->user()->patient;
        $reminders = $this->remindersFor($patient->id);

        return view('patient.notifications', compact('reminders'));
    }

    private function remindersFor(int $patientId)
    {
        $reminders = ControlSchedule::remindersFor($patientId)->pluck('text');

        if ($queueReminder = Queue::reminderTextFor($patientId)) {
            $reminders->push($queueReminder);
        }

        return $reminders;
    }

    public function queue(Request $request)
    {
        $patient = $request->user()->patient;
        $activeQueue = Queue::activeToday($patient->id);
        $activeRooms = Room::active();

        return view('patient.queue', compact('patient', 'activeQueue', 'activeRooms'));
    }

    public function takeQueue(Request $request)
    {
        $validated = $request->validate([
            'room_id' => ['nullable', 'exists:rooms,id'],
            'complaint' => ['nullable', 'string', 'max:1000'],
        ]);

        return $this->createQueue($request, 'online', $validated['room_id'] ?? null, $validated['complaint'] ?? null);
    }

    public function queueStatus(Request $request)
    {
        $patient = $request->user()->patient;
        $queue = Queue::activeToday($patient->id);

        if (! $queue) {
            return response()->json(['active' => false]);
        }

        return response()->json([
            'active' => true,
            'queue_number' => $queue->queue_number,
            'status' => $queue->status,
            'status_label' => $queue->status_label,
            'status_badge_class' => $queue->status_badge_class,
            'ahead' => $queue->patientsAhead(),
            'estimate_minutes' => $queue->estimatedWaitMinutes(),
        ]);
    }

    public function checkIn(Request $request)
    {
        $patient = $request->user()->patient;
        $activeQueue = Queue::activeToday($patient->id);
        $activeRooms = Room::active();

        return view('patient.check-in', compact('patient', 'activeQueue', 'activeRooms'));
    }

    public function checkInSubmit(Request $request)
    {
        $validated = $request->validate([
            'room_id' => ['nullable', 'exists:rooms,id'],
            'complaint' => ['nullable', 'string', 'max:1000'],
        ]);

        return $this->createQueue($request, 'qr', $validated['room_id'] ?? null, $validated['complaint'] ?? null);
    }

    public function history(Request $request)
    {
        $patient = $request->user()->patient;

        $visits = $patient->visits()
            ->with(['doctor', 'treatments'])
            ->latest('visit_date')
            ->latest('id')
            ->get();

        return view('patient.history', compact('patient', 'visits'));
    }

    public function odontogram(Request $request)
    {
        $patient = $request->user()->patient;

        $odontograms = $patient->odontograms()
            ->with(['visit', 'doctor', 'teeth'])
            ->latest('examination_date')
            ->latest('id')
            ->get();

        $selectedOdontogram = $request->filled('odontogram_id')
            ? $odontograms->firstWhere('id', (int) $request->query('odontogram_id'))
            : $odontograms->first();

        return view('patient.odontogram', compact('patient', 'odontograms', 'selectedOdontogram'));
    }

    public function odontogramCompare(Request $request)
    {
        $patient = $request->user()->patient;

        $odontograms = $patient->odontograms()
            ->with('teeth')
            ->latest('examination_date')
            ->latest('id')
            ->limit(2)
            ->get();

        $current = $odontograms->first();
        $previous = $odontograms->get(1);

        abort_unless($current, 404, 'Belum ada data odontogram.');

        $changes = Odontogram::diffTeeth($current, $previous);

        return view('patient.odontogram-compare', compact('patient', 'current', 'previous', 'changes'));
    }

    public function profile(Request $request)
    {
        $patient = $request->user()->patient;

        return view('patient.profile', compact('patient'));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $patient = $user->patient;

        $validated = $request->validate([
            'address' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [], ['address' => 'alamat', 'phone' => 'no. HP', 'email' => 'email', 'password' => 'kata sandi']);

        $patient->update([
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        $userUpdate = ['email' => $validated['email']];
        if (! empty($validated['password'])) {
            $userUpdate['password'] = $validated['password'];
        }
        $user->update($userUpdate);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    private function createQueue(Request $request, string $source, ?int $roomId = null, ?string $complaint = null)
    {
        $patient = $request->user()->patient;

        if (Queue::hasActiveToday($patient->id)) {
            return redirect()->route('patient.queue')->with('error', 'Anda sudah memiliki antrean aktif hari ini.');
        }

        $queue = Queue::createForPatient($patient->id, $source, $roomId, $complaint);

        return redirect()->route('patient.queue')->with('success', "Antrean {$queue->queue_number} berhasil dibuat.");
    }
}
