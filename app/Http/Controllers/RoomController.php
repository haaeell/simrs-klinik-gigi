<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with('doctor')->orderBy('name')->get();

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

        Room::create($validated);

        return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function edit(Room $room)
    {
        $doctors = $this->availableDoctors($room);

        return view('rooms.edit', compact('room', 'doctors'));
    }

    public function update(Request $request, Room $room)
    {
        $validated = $this->validateRoom($request, $room);

        $room->update($validated);

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
}
