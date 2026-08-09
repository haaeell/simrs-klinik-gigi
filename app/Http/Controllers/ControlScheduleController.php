<?php

namespace App\Http\Controllers;

use App\Models\ControlSchedule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ControlScheduleController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'scheduled');

        $schedules = ControlSchedule::with(['patient', 'doctor', 'visit'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->orderBy('control_date')
            ->get();

        return view('control-schedules.index', compact('schedules', 'status'));
    }

    public function update(Request $request, ControlSchedule $controlSchedule)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['completed', 'cancelled'])],
        ]);

        $controlSchedule->update($validated);

        $message = $validated['status'] === 'completed'
            ? 'Jadwal kontrol ditandai selesai.'
            : 'Jadwal kontrol dibatalkan.';

        return back()->with('success', $message);
    }
}
