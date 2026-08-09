@extends('layouts.app')

@section('title', 'Jadwal Kontrol')
@section('page-title', 'Jadwal Kontrol')
@section('page-description', 'Kelola jadwal kontrol pasien yang dianjurkan dokter')

@section('content')
    @php
        $tabs = ['scheduled' => 'Terjadwal', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan', 'all' => 'Semua'];
    @endphp

    <div class="mb-6 flex gap-1 overflow-x-auto border-b border-slate-200">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('control-schedules.index', ['status' => $key]) }}"
                class="flex shrink-0 items-center gap-2 rounded-t-lg px-4 py-2.5 text-sm font-medium
                    {{ $status === $key ? 'bg-blue-50 text-blue-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Tanggal Kontrol</th>
                        <th class="px-5 py-3 font-medium">Pasien</th>
                        <th class="px-5 py-3 font-medium">Dokter</th>
                        <th class="px-5 py-3 font-medium">Catatan</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($schedules as $schedule)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-slate-900">{{ $schedule->control_date->translatedFormat('d F Y') }}</p>
                                @if ($schedule->reminder_text)
                                    <p class="text-xs font-medium text-amber-600">{{ $schedule->reminder_text }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <a href="{{ route('patients.show', $schedule->patient) }}" class="font-medium text-slate-900 hover:text-blue-600" title="Lihat detail pasien">
                                    {{ $schedule->patient->name }}
                                </a>
                                <p class="text-xs text-slate-400">{{ $schedule->patient->medical_record_number }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-slate-500">{{ $schedule->doctor->name }}</td>
                            <td class="px-5 py-3.5 text-slate-500">{{ $schedule->notes ?: '-' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $schedule->status_badge_class }}">
                                    {{ $schedule->status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if ($schedule->status === 'scheduled')
                                        <form action="{{ route('control-schedules.update', $schedule) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" title="Tandai pasien sudah datang kontrol"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                                <i class="fa-solid fa-check"></i> Selesai
                                            </button>
                                        </form>
                                        <form action="{{ route('control-schedules.update', $schedule) }}" method="POST"
                                            data-confirm data-confirm-title="Batalkan Jadwal Kontrol?"
                                            data-confirm-text="Jadwal kontrol {{ $schedule->patient->name }} tanggal {{ $schedule->control_date->translatedFormat('d F Y') }} akan dibatalkan.">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" title="Batalkan jadwal kontrol ini"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
                                                <i class="fa-solid fa-xmark"></i> Batalkan
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-300">&mdash;</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <i class="fa-solid fa-calendar-check mb-3 block text-3xl text-slate-300"></i>
                                <p class="text-sm font-medium text-slate-500">Tidak ada jadwal kontrol.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
