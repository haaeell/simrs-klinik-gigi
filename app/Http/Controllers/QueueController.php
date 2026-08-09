<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $queues = Queue::with('patient')
            ->where('queue_date', $today)
            ->orderBy('queue_number')
            ->get();

        $counts = $this->countByStatus($queues);

        // Computed once and reused per-row in the view — the average is the same for every queue today.
        $avgExaminationMinutes = Queue::averageExaminationMinutes();

        return view('queues.index', [
            'queues' => $queues,
            'counts' => $counts,
            'avgExaminationMinutes' => $avgExaminationMinutes,
            'today' => $today,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
        ]);

        if (Queue::hasActiveToday($validated['patient_id'])) {
            return back()->with('error', 'Pasien ini sudah memiliki antrean aktif hari ini.');
        }

        $queue = Queue::createForPatient($validated['patient_id'], 'staff');

        return redirect()->route('queues.print', $queue)->with('success', "Antrean {$queue->queue_number} berhasil dibuat.");
    }

    public function call(Queue $queue)
    {
        if (! in_array($queue->status, ['waiting', 'skipped'], true)) {
            return response()->json(['message' => 'Antrean ini tidak dapat dipanggil.'], 422);
        }

        $queue->update(['status' => 'called', 'called_at' => now()]);

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

        $visit = DB::transaction(function () use ($queue, $request) {
            $queue->update(['status' => 'examining', 'started_at' => now()]);

            return Visit::create([
                'patient_id' => $queue->patient_id,
                'queue_id' => $queue->id,
                'doctor_id' => $request->user()->id,
                'visit_date' => now()->toDateString(),
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

        $current = Queue::with('patient')
            ->where('queue_date', $today)
            ->where('status', 'called')
            ->orderByDesc('called_at')
            ->first();

        $next = Queue::where('queue_date', $today)
            ->where('status', 'waiting')
            ->orderBy('queue_number')
            ->limit(3)
            ->pluck('queue_number');

        return [
            'current' => $current ? [
                'queue_number' => $current->queue_number,
                'patient_name' => $current->patient->name,
            ] : null,
            'next' => $next,
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
