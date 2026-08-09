@extends('layouts.app')

@section('title', 'Riwayat Kunjungan')
@section('page-title', 'Riwayat Kunjungan')
@section('page-description', 'Daftar kunjungan pemeriksaan Anda')

@section('content')
    <div class="space-y-4">
        @forelse ($visits as $visit)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm font-semibold text-slate-900">{{ $visit->visit_date->translatedFormat('d F Y') }}</p>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $visit->status_badge_class }}">
                        {{ $visit->status_label }}
                    </span>
                </div>
                <p class="mt-1 text-xs text-slate-500">Dokter: {{ $visit->doctor->name }}</p>

                <div class="mt-3 space-y-1.5 border-t border-slate-100 pt-3 text-sm">
                    <p class="text-slate-700"><span class="text-slate-400">Keluhan:</span> {{ $visit->complaint ?: '-' }}</p>
                    <p class="text-slate-700"><span class="text-slate-400">Diagnosis:</span> {{ $visit->diagnosis ?: '-' }}</p>
                </div>

                @if ($visit->treatments->isNotEmpty())
                    <div class="mt-3 flex flex-wrap gap-1.5 border-t border-slate-100 pt-3">
                        @foreach ($visit->treatments as $treatment)
                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                                @if ($treatment->tooth_number) Gigi {{ $treatment->tooth_number }} &middot; @endif {{ $treatment->treatment }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm">
                <i class="fa-solid fa-clock-rotate-left mb-3 block text-3xl text-slate-300"></i>
                <p class="text-sm text-slate-500">Belum ada riwayat kunjungan.</p>
            </div>
        @endforelse
    </div>
@endsection
