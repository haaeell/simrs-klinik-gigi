<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with(['doctor', 'schedules'])->orderBy('name')->get();

        return view('rooms.index', compact('rooms'));
    }

    public function create()
    {
        $doctors = $this->availableDoctors();

        return view('rooms.create', compact('doctors'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRoom($request);

        $room = Room::create($validated);
        $this->syncSchedules($room, $request);

        return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function edit(Room $room)
    {
        $doctors = $this->availableDoctors($room);
        $room->load('schedules');

        return view('rooms.edit', compact('room', 'doctors'));
    }

    public function update(Request $request, Room $room)
    {
        $validated = $this->validateRoom($request, $room);

        $room->update($validated);
        $this->syncSchedules($room, $request);

        return redirect()->route('rooms.index')->with('success', 'Data ruangan berhasil diperbarui.');
    }

    public function destroy(Room $room)
    {
        $room->delete();

        return back()->with('success', 'Ruangan berhasil dihapus.');
    }

    private function availableDoctors(?Room $room = null)
    {
        return User::where('role', 'dokter')
            ->where(function ($query) use ($room) {
                $query->whereDoesntHave('room');
                if ($room?->doctor_id) {
                    $query->orWhere('id', $room->doctor_id);
                }
            })
            ->orderBy('name')
            ->get();
    }

    private function validateRoom(Request $request, ?Room $room = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'doctor_id' => [
                'nullable', 'exists:users,id',
                Rule::unique('rooms', 'doctor_id')->ignore($room?->id),
            ],
        ], [], [
            'name' => 'nama ruangan',
            'doctor_id' => 'dokter',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    /**
     * Replace the room's weekly schedule from the "schedule[{day}][enabled|start_time|end_time]"
     * form fields — one row per day of week (0=Minggu..6=Sabtu), unchecked days are removed.
     */
    private function syncSchedules(Room $room, Request $request): void
    {
        $input = $request->input('schedule', []);

        foreach (RoomSchedule::DAYS as $day => $label) {
            $day = (int) $day;
            $enabled = ! empty($input[$day]['enabled'] ?? null);

            if (! $enabled) {
                $room->schedules()->where('day_of_week', $day)->delete();

                continue;
            }

            $request->validate([
                "schedule.$day.start_time" => ['required', 'date_format:H:i'],
                "schedule.$day.end_time" => ['required', 'date_format:H:i', "after:schedule.$day.start_time"],
            ], [], ["schedule.$day.start_time" => "jam mulai $label", "schedule.$day.end_time" => "jam selesai $label"]);

            $room->schedules()->updateOrCreate(
                ['day_of_week' => $day],
                ['start_time' => $input[$day]['start_time'], 'end_time' => $input[$day]['end_time']],
            );
        }
    }
}
