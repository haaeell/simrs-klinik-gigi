@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Selamat datang, ' . $patient->name)

@section('content')
    <div class="space-y-5">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs text-slate-400">No. Rekam Medis</p>
                    <p class="font-mono text-lg font-semibold text-slate-900">{{ $patient->medical_record_number }}</p>
                </div>
                @if ($activeQueue)
                    <a href="{{ route('patient.queue') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                        <i class="fa-solid fa-list-ol"></i> Lihat Antrean Saya
                    </a>
                @else
                    <form method="POST" action="{{ route('patient.queue.take') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                            <i class="fa-solid fa-ticket"></i> Ambil Antrean
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <i class="fa-solid fa-list-ol"></i> Antrean Aktif
                </p>
                @if ($activeQueue)
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-2xl font-bold text-blue-600">{{ $activeQueue->queue_number }}</p>
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $activeQueue->status_badge_class }}">
                                {{ $activeQueue->status_label }}
                            </span>
                        </div>
                        <a href="{{ route('patient.queue') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">Detail <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                @else
                    <p class="text-sm text-slate-500">Belum ada antrean aktif hari ini.</p>
                @endif
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <i class="fa-solid fa-calendar-check"></i> Jadwal Kontrol Berikutnya
                </p>
                @if ($patient->nextControlSchedule)
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-slate-900">{{ $patient->nextControlSchedule->control_date->translatedFormat('d F Y') }}</p>
                        <a href="{{ route('patient.control-schedules') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700">Detail <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                @else
                    <p class="text-sm text-slate-500">Tidak ada jadwal kontrol.</p>
                @endif
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                <i class="fa-solid fa-clock-rotate-left"></i> Kunjungan Terakhir
            </p>
            @if ($patient->latestVisit)
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $patient->latestVisit->visit_date->translatedFormat('d F Y') }}</p>
                        <p class="text-xs text-slate-500">Dokter: {{ $patient->latestVisit->doctor->name }}</p>
                        @if ($patient->latestVisit->diagnosis)
                            <p class="mt-1 text-sm text-slate-600">Diagnosis: {{ $patient->latestVisit->diagnosis }}</p>
                        @endif
                    </div>
                </div>
            @else
                <p class="text-sm text-slate-500">Belum ada riwayat kunjungan.</p>
            @endif
        </div>

        @if ($reminders->isNotEmpty())
            <div class="space-y-2">
                @foreach ($reminders as $reminder)
                    <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4 text-sm font-medium text-amber-700">
                        <i class="fa-solid fa-circle-exclamation mr-1.5"></i> {{ $reminder }}
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-700">
                <i class="fa-solid fa-circle-info mr-1.5"></i>
                Belum ada pengingat saat ini.
            </div>
        @endif
    </div>
@endsection
