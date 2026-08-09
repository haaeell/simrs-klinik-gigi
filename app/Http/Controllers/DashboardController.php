<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Queue;
use App\Models\Visit;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->toDateString();
        $todayQueues = Queue::where('queue_date', $today)->get();

        $counts = [
            'today' => $todayQueues->count(),
            'waiting' => $todayQueues->where('status', 'waiting')->count(),
            'examining' => $todayQueues->where('status', 'examining')->count(),
            'done' => $todayQueues->where('status', 'done')->count(),
        ];

        if ($request->user()->isAdmin()) {
            $queueRows = Queue::with('patient')
                ->where('queue_date', $today)
                ->orderBy('queue_number')
                ->limit(8)
                ->get();

            $recentVisits = Visit::with(['patient', 'doctor'])
                ->latest('visit_date')
                ->latest('id')
                ->limit(5)
                ->get();

            $sourceCounts = collect(Queue::SOURCES)->keys()
                ->mapWithKeys(fn ($source) => [$source => $todayQueues->where('registration_source', $source)->count()]);

            return view('dashboard.index', [
                'counts' => $counts + [
                    'total_patients' => Patient::count(),
                    'patients_today' => $todayQueues->pluck('patient_id')->unique()->count(),
                    'avg_wait_minutes' => Queue::averageWaitMinutesToday(),
                    'avg_examination_minutes' => Queue::averageExaminationMinutes(),
                ],
                'sourceCounts' => $sourceCounts,
                'queueRows' => $queueRows,
                'recentVisits' => $recentVisits,
            ]);
        }

        $nextQueues = Queue::with(['patient', 'room'])
            ->where('queue_date', $today)
            ->whereIn('status', ['waiting', 'called'])
            ->orderBy('queue_number')
            ->limit(8)
            ->get();

        return view('dashboard.index', [
            'counts' => $counts,
            'nextQueues' => $nextQueues,
        ]);
    }
}
