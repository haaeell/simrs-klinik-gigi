<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Queue;
use App\Models\Treatment;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;

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

        return view('reports.visits', compact('visits', 'summary', 'doctors', 'start', 'end', 'doctorId'));
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

        return view('reports.queues', compact('queues', 'summary', 'start', 'end'));
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

        return view('reports.treatments', compact('treatments', 'doctors', 'start', 'end', 'doctorId', 'keyword'));
    }

    private function dateRange(Request $request): array
    {
        $start = $request->input('start_date') ?: now()->startOfMonth()->toDateString();
        $end = $request->input('end_date') ?: now()->toDateString();

        return [$start, $end];
    }
}
