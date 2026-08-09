@extends('layouts.app')

@section('title', 'Jadwal Kontrol')
@section('page-title', 'Jadwal Kontrol')
@section('page-description', 'Jadwal kontrol yang direkomendasikan dokter')

@section('content')
    <div class="space-y-4">
        @forelse ($schedules as $schedule)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm font-semibold text-slate-900">{{ $schedule->control_date->translatedFormat('d F Y') }}</p>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $schedule->status_badge_class }}">
                        {{ $schedule->status_label }}
                    </span>
                </div>
                @if ($schedule->reminder_text)
                    <p class="mt-2 text-xs font-medium text-amber-600">
                        <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $schedule->reminder_text }}
                    </p>
                @endif
                <p class="mt-2 text-sm text-slate-600">{{ $schedule->notes ?: 'Tidak ada catatan tambahan.' }}</p>
                @if ($schedule->visit)
                    <p class="mt-2 text-xs text-slate-400">Berdasarkan kunjungan {{ $schedule->visit->visit_date->translatedFormat('d F Y') }}</p>
                @endif
            </div>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm">
                <i class="fa-solid fa-calendar-check mb-3 block text-3xl text-slate-300"></i>
                <p class="text-sm text-slate-500">Belum ada jadwal kontrol.</p>
            </div>
        @endforelse
    </div>
@endsection
