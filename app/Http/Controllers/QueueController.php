<?php

namespace App\Http\Controllers;

use App\Models\DisplaySetting;
use App\Models\Patient;
use App\Models\Queue;
use App\Models\Room;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $queues = Queue::with(['patient', 'room'])
            ->where('queue_date', $today)
            ->orderBy('queue_number')
            ->get();

        $counts = $this->countByStatus($queues);

        // Computed once and reused per-row in the view — the average is the same for every queue today.
        $avgExaminationMinutes = Queue::averageExaminationMinutes();
        $activeRooms = Room::activeWithSchedules();

        return view('queues.index', [
            'queues' => $queues,
            'counts' => $counts,
            'avgExaminationMinutes' => $avgExaminationMinutes,
            'activeRooms' => $activeRooms,
            'today' => $today,
        ]);
    }

    /**
     * Booking form — staff picks the doctor/room and jots the complaint down before the
     * patient even sits in the waiting area, so "Panggil" later needs no extra questions.
     */
    public function create(Request $request)
    {
        $patient = Patient::findOrFail($request->query('patient_id'));

        if (Queue::hasActiveToday($patient->id)) {
            return redirect()->route('patients.show', $patient)->with('error', 'Pasien ini sudah memiliki antrean aktif hari ini.');
        }

        $activeRooms = Room::activeWithSchedules();

        return view('queues.create', compact('patient', 'activeRooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'complaint' => ['nullable', 'string', 'max:1000'],
        ]);

        if (Queue::hasActiveToday($validated['patient_id'])) {
            return back()->with('error', 'Pasien ini sudah memiliki antrean aktif hari ini.');
        }

        $queue = Queue::createForPatient(
            $validated['patient_id'],
            'staff',
            $validated['room_id'] ?? null,
            $validated['complaint'] ?? null,
        );

        return redirect()->route('queues.print', $queue)->with('success', "Antrean {$queue->queue_number} berhasil dibuat.");
    }

    public function call(Request $request, Queue $queue)
    {
        if (! in_array($queue->status, ['waiting', 'skipped'], true)) {
            return response()->json(['message' => 'Antrean ini tidak dapat dipanggil.'], 422);
        }

        // The doctor/room is normally already picked at booking time. Only ask again here
        // for queues that never got one (online/QR self check-in, or legacy walk-ins).
        $roomId = $queue->room_id ?: $request->input('room_id');

        if ($roomId && ! Room::where('is_active', true)->whereNotNull('doctor_id')->whereKey($roomId)->exists()) {
            return response()->json(['message' => 'Ruangan tidak valid.'], 422);
        }

        $queue->update(['status' => 'called', 'called_at' => now(), 'room_id' => $roomId ?: null]);

        return $this->actionResponse($queue);
    }

    public function recall(Queue $queue)
    {
        if ($queue->status !== 'called') {
            return response()->json(['message' => 'Antrean ini tidak sedang dipanggil.'], 422);
        }

        $queue->update(['called_at' => now()]);

        return $this->actionResponse($queue);
    }

    public function skip(Queue $queue)
    {
        if (! in_array($queue->status, ['waiting', 'called'], true)) {
            return response()->json(['message' => 'Antrean ini tidak dapat dilewati.'], 422);
        }

        $queue->update(['status' => 'skipped']);

        return $this->actionResponse($queue);
    }

    public function startExamination(Request $request, Queue $queue)
    {
        abort_unless($request->user()->isDokter(), 403, 'Hanya dokter yang dapat memulai pemeriksaan.');

        if ($queue->status !== 'called') {
            return back()->with('error', 'Antrean ini harus dipanggil terlebih dahulu sebelum pemeriksaan dimulai.');
        }

        // If the call was routed to a specific room, only that room's doctor may pick it up.
        abort_if(
            $queue->room_id && $queue->room->doctor_id !== $request->user()->id,
            403,
            'Antrean ini dipanggil untuk ruangan dokter lain.'
        );

        $visit = DB::transaction(function () use ($queue, $request) {
            $queue->update(['status' => 'examining', 'started_at' => now()]);

            return Visit::create([
                'patient_id' => $queue->patient_id,
                'queue_id' => $queue->id,
                'doctor_id' => $request->user()->id,
                'visit_date' => now()->toDateString(),
                'complaint' => $queue->complaint,
                'status' => 'examining',
            ]);
        });

        return redirect()->route('visits.show', $visit);
    }

    public function print(Queue $queue)
    {
        $queue->load('patient');

        return view('queues.print', compact('queue'));
    }

    public function display()
    {
        return view('queues.display', $this->displayPayload());
    }

    public function displayData()
    {
        return response()->json($this->displayPayload());
    }

    private function displayPayload(): array
    {
        $today = now()->toDateString();
        $setting = DisplaySetting::current();
        $activeRooms = Room::active();

        $describeCurrent = function (Queue $current, ?string $roomName = null) use ($setting) {
            return [
                'queue_number' => $current->queue_number,
                'patient_name' => $current->patient->name,
                'called_at' => $current->called_at?->toIso8601String(),
                'announcement' => $setting->renderAnnouncement($current->queue_number, $current->patient->name, $roomName),
            ];
        };

        if ($activeRooms->isNotEmpty()) {
            // One card per configured room — each shows whoever is currently called into it,
            // so several doctors can call patients in parallel.
            $rooms = $activeRooms->map(function (Room $room) use ($today, $describeCurrent) {
                $current = Queue::with('patient')
                    ->where('queue_date', $today)
                    ->where('room_id', $room->id)
                    ->where('status', 'called')
                    ->orderByDesc('called_at')
                    ->first();

                return [
                    'room_id' => $room->id,
                    'room_name' => $room->name,
                    'current' => $current ? $describeCurrent($current, $room->name) : null,
                ];
            })->values();
        } else {
            // No rooms configured yet — fall back to the original single-number display.
            $current = Queue::with('patient')
                ->where('queue_date', $today)
                ->where('status', 'called')
                ->orderByDesc('called_at')
                ->first();

            $rooms = collect([[
                'room_id' => null,
                'room_name' => null,
                'current' => $current ? $describeCurrent($current) : null,
            ]]);
        }

        $next = Queue::where('queue_date', $today)
            ->where('status', 'waiting')
            ->orderBy('queue_number')
            ->limit(3)
            ->pluck('queue_number');

        return [
            'rooms' => $rooms,
            'next' => $next,
            'settings' => [
                'chime_style' => $setting->chime_style,
                'voice_name' => $setting->voice_name,
                'voice_lang' => $setting->voice_lang,
                'voice_rate' => $setting->voice_rate,
                'voice_pitch' => $setting->voice_pitch,
            ],
        ];
    }

    private function actionResponse(Queue $queue)
    {
        return response()->json([
            'queue' => [
                'id' => $queue->id,
                'status' => $queue->status,
                'status_label' => $queue->status_label,
                'status_badge_class' => $queue->status_badge_class,
                'room_name' => $queue->room?->name,
            ],
            'counts' => $this->countByStatus(
                Queue::where('queue_date', $queue->queue_date->toDateString())->get()
            ),
        ]);
    }

    private function countByStatus($queues): array
    {
        return [
            'waiting' => $queues->where('status', 'waiting')->count(),
            'called' => $queues->where('status', 'called')->count(),
            'examining' => $queues->where('status', 'examining')->count(),
            'done' => $queues->where('status', 'done')->count(),
        ];
    }
}
