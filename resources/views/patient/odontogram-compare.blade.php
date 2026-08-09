@extends('layouts.app')

@section('title', 'Bandingkan Odontogram')
@section('page-title', 'Bandingkan Odontogram')
@section('page-description', 'Perbandingan dua pemeriksaan terakhir')

@section('content')
    <div class="mb-4">
        <a href="{{ route('patient.odontogram') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-700">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Odontogram
        </a>
    </div>

    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs text-slate-400">Pemeriksaan Terbaru</p>
            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $current->examination_date->translatedFormat('d F Y') }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs text-slate-400">Pemeriksaan Sebelumnya</p>
            @if ($previous)
                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $previous->examination_date->translatedFormat('d F Y') }}</p>
            @else
                <p class="mt-1 text-sm text-slate-400">Belum ada pemeriksaan sebelumnya.</p>
            @endif
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="mb-5 text-sm font-semibold text-slate-900">Perubahan Kondisi Gigi</h3>
        @include('partials.odontogram-changes')
    </div>
@endsection
