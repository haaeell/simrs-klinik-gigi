@extends('layouts.app')

@section('title', 'Ruangan')
@section('page-title', 'Ruangan Pemeriksaan')
@section('page-description', 'Kelola ruangan dan dokter yang bertugas di dalamnya')

@section('content')
    <div class="mb-5 flex justify-end">
        <a href="{{ route('rooms.create') }}" title="Tambah ruangan pemeriksaan baru"
            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700">
            <i class="fa-solid fa-plus"></i>
            Tambah Ruangan
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">No</th>
                        <th class="px-5 py-3 font-medium">Ruangan</th>
                        <th class="px-5 py-3 font-medium">Dokter</th>
                        <th class="px-5 py-3 font-medium">Jadwal Praktik</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rooms as $room)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-3.5 text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-5 py-3.5 font-medium text-slate-900">{{ $room->name }}</td>
                            <td class="px-5 py-3.5 text-slate-500">
                                @if ($room->doctor)
                                    <span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-user-doctor text-slate-400"></i> {{ $room->doctor->name }}</span>
                                @else
                                    <span class="text-slate-400">Belum ditentukan</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-slate-500">
                                @if ($room->schedules->isEmpty())
                                    <span class="text-slate-400">Belum diatur</span>
                                @else
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($room->schedules->sortBy('day_of_week') as $schedule)
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">
                                                {{ substr(\App\Models\RoomSchedule::DAYS[$schedule->day_of_week], 0, 3) }} {{ $schedule->start_time->format('H:i') }}-{{ $schedule->end_time->format('H:i') }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($room->is_active)
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">Aktif</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('rooms.edit', $room) }}"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-blue-600" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('rooms.destroy', $room) }}" method="POST" data-confirm
                                        data-confirm-title="Hapus Ruangan?"
                                        data-confirm-text="Ruangan {{ $room->name }} akan dihapus. Antrean lama yang tercatat di ruangan ini tidak akan terhapus.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-red-50 hover:text-red-600" title="Hapus">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <i class="fa-solid fa-door-open mb-3 block text-3xl text-slate-300"></i>
                                <p class="text-sm font-medium text-slate-500">Belum ada ruangan.</p>
                                <p class="mt-1 text-xs text-slate-400">Tanpa ruangan, pemanggilan antrean tetap berjalan seperti biasa (satu nomor panggilan umum).</p>
                                <a href="{{ route('rooms.create') }}" class="mt-3 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                    <i class="fa-solid fa-plus"></i> Tambah Ruangan
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
