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

            // Last 7 days of queue volume, for the "Tren Kunjungan" chart — one grouped
            // query, then filled in so days with zero queues still show up as a bar.
            $countsByDate = Queue::where('queue_date', '>=', now()->subDays(6)->toDateString())
                ->selectRaw('queue_date, count(*) as total')
                ->groupBy('queue_date')
                ->pluck('total', 'queue_date');

            $visitTrend = collect(range(6, 0))->map(function ($daysAgo) use ($countsByDate) {
                $date = now()->subDays($daysAgo);

                return [
                    'label' => $date->translatedFormat('d M'),
                    'total' => (int) ($countsByDate[$date->toDateString()] ?? 0),
                ];
            });

            return view('dashboard.index', [
                'counts' => $counts + [
                    'total_patients' => Patient::count(),
                    'patients_today' => $todayQueues->pluck('patient_id')->unique()->count(),
                    'avg_wait_minutes' => Queue::averageWaitMinutesToday(),
                    'avg_examination_minutes' => Queue::averageExaminationMinutes(),
                ],
                'sourceCounts' => $sourceCounts,
                'visitTrend' => $visitTrend,
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
