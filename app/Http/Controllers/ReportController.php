<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Queue;
use App\Models\Treatment;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function visits(Request $request)
    {
        [$start, $end] = $this->dateRange($request);
        $doctorId = $request->input('doctor_id');

        $visits = Visit::with(['patient', 'doctor'])
            ->whereBetween('visit_date', [$start, $end])
            ->when($doctorId, fn ($q) => $q->where('doctor_id', $doctorId))
            ->orderBy('visit_date')
            ->orderBy('id')
            ->get();

        $patientIds = $visits->pluck('patient_id')->unique();

        $firstVisitDates = Visit::selectRaw('patient_id, MIN(visit_date) as first_date')
            ->whereIn('patient_id', $patientIds)
            ->groupBy('patient_id')
            ->pluck('first_date', 'patient_id');

        $newPatientCount = $patientIds->filter(function ($id) use ($firstVisitDates, $start, $end) {
            $firstDate = $firstVisitDates[$id] ?? null;

            return $firstDate && $firstDate >= $start && $firstDate <= $end;
        })->count();

        $summary = [
            'total' => $visits->count(),
            'new_patients' => $newPatientCount,
            'old_patients' => $patientIds->count() - $newPatientCount,
        ];

        $doctors = User::where('role', 'dokter')->orderBy('name')->get();
        $dailyVisits = $this->dailyCounts($visits, fn ($visit) => $visit->visit_date, $start, $end);

        return view('reports.visits', compact('visits', 'summary', 'doctors', 'start', 'end', 'doctorId', 'dailyVisits'));
    }

    public function queues(Request $request)
    {
        [$start, $end] = $this->dateRange($request);

        $queues = Queue::with('patient')
            ->whereBetween('queue_date', [$start, $end])
            ->orderBy('queue_date')
            ->orderBy('queue_number')
            ->get();

        $summary = [
            'total' => $queues->count(),
            'done' => $queues->where('status', 'done')->count(),
            'skipped' => $queues->where('status', 'skipped')->count(),
        ];

        $dailyQueues = $this->dailyCounts($queues, fn ($queue) => $queue->queue_date, $start, $end);

        return view('reports.queues', compact('queues', 'summary', 'start', 'end', 'dailyQueues'));
    }

    public function patients(Request $request)
    {
        $search = $request->input('search');

        $patients = Patient::withCount('visits')
            ->with('latestVisit')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('medical_record_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get();

        $summary = [
            'total' => Patient::count(),
            'new_this_month' => Patient::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
        ];

        return view('reports.patients', compact('patients', 'summary', 'search'));
    }

    public function treatments(Request $request)
    {
        [$start, $end] = $this->dateRange($request);
        $doctorId = $request->input('doctor_id');
        $keyword = $request->input('keyword');

        $treatments = Treatment::with(['visit.patient', 'visit.doctor'])
            ->whereHas('visit', function ($query) use ($start, $end, $doctorId) {
                $query->whereBetween('visit_date', [$start, $end]);
                if ($doctorId) {
                    $query->where('doctor_id', $doctorId);
                }
            })
            ->when($keyword, fn ($q) => $q->where('treatment', 'like', "%{$keyword}%"))
            ->orderByDesc('id')
            ->get();

        $doctors = User::where('role', 'dokter')->orderBy('name')->get();

        $topTreatments = $treatments->groupBy('treatment')
            ->map->count()
            ->sortDesc()
            ->take(8);

        return view('reports.treatments', compact('treatments', 'doctors', 'start', 'end', 'doctorId', 'keyword', 'topTreatments'));
    }

    public function serviceTime(Request $request)
    {
        [$start, $end] = $this->dateRange($request);
        $doctorId = $request->input('doctor_id');
        $source = $request->input('source');

        $queues = Queue::with(['patient', 'visit.doctor'])
            ->whereBetween('queue_date', [$start, $end])
            ->when($source, fn ($q) => $q->where('registration_source', $source))
            ->when($doctorId, fn ($q) => $q->whereHas('visit', fn ($v) => $v->where('doctor_id', $doctorId)))
            ->orderBy('queue_date')
            ->orderBy('queue_number')
            ->get();

        $waitMinutes = $queues->filter(fn ($q) => $q->called_at)
            ->map(fn ($q) => $q->created_at->diffInMinutes($q->called_at))
            ->filter(fn ($minutes) => $minutes >= 0);

        $examinationMinutes = $queues->filter(fn ($q) => $q->started_at && $q->finished_at)
            ->map(fn ($q) => $q->started_at->diffInMinutes($q->finished_at))
            ->filter(fn ($minutes) => $minutes >= 0);

        $sourceCounts = collect(Queue::SOURCES)->keys()
            ->mapWithKeys(fn ($key) => [$key => $queues->where('registration_source', $key)->count()]);

        $summary = [
            'total' => $queues->count(),
            'avg_wait_minutes' => $waitMinutes->isEmpty() ? 0 : (int) round($waitMinutes->avg()),
            'avg_examination_minutes' => $examinationMinutes->isEmpty() ? 0 : (int) round($examinationMinutes->avg()),
            'source_counts' => $sourceCounts,
        ];

        $doctors = User::where('role', 'dokter')->orderBy('name')->get();

        $dailyServiceTime = $queues->groupBy(fn ($queue) => $queue->queue_date->format('Y-m-d'))
            ->map(function ($dayQueues) {
                $wait = $dayQueues->filter(fn ($q) => $q->called_at)->map(fn ($q) => $q->created_at->diffInMinutes($q->called_at))->filter(fn ($m) => $m >= 0);
                $exam = $dayQueues->filter(fn ($q) => $q->started_at && $q->finished_at)->map(fn ($q) => $q->started_at->diffInMinutes($q->finished_at))->filter(fn ($m) => $m >= 0);

                return [
                    'wait' => $wait->isEmpty() ? 0 : (int) round($wait->avg()),
                    'examination' => $exam->isEmpty() ? 0 : (int) round($exam->avg()),
                ];
            });

        $dailyServiceTime = collect(Carbon::parse($start)->daysUntil(Carbon::parse($end)))
            ->map(fn ($date) => [
                'label' => $date->translatedFormat('d M'),
                'wait' => $dailyServiceTime[$date->format('Y-m-d')]['wait'] ?? 0,
                'examination' => $dailyServiceTime[$date->format('Y-m-d')]['examination'] ?? 0,
            ]);

        return view('reports.service-time', compact('queues', 'summary', 'doctors', 'start', 'end', 'doctorId', 'source', 'dailyServiceTime'));
    }

    private function dateRange(Request $request): array
    {
        $start = $request->input('start_date') ?: now()->startOfMonth()->toDateString();
        $end = $request->input('end_date') ?: now()->toDateString();

        return [$start, $end];
    }

    /**
     * Day-by-day totals for a bar chart — every day in [start, end] shows up (0 if empty),
     * built once here so each report's chart uses the same fill-the-gaps logic.
     */
    private function dailyCounts($items, \Closure $dateAccessor, string $start, string $end)
    {
        $byDate = $items->groupBy(fn ($item) => $dateAccessor($item)->format('Y-m-d'))->map->count();

        return collect(Carbon::parse($start)->daysUntil(Carbon::parse($end)))
            ->map(fn ($date) => [
                'label' => $date->translatedFormat('d M'),
                'total' => $byDate[$date->format('Y-m-d')] ?? 0,
            ]);
    }
}
