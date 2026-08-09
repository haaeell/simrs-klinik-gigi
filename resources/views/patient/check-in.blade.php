@extends('layouts.app')

@section('title', 'Check-in')
@section('page-title', 'Check-in Klinik')
@section('page-description', 'Ambil antrean langsung dari klinik')

@section('content')
    <div class="mx-auto max-w-md">
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
            <span class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-2xl text-blue-600">
                <i class="fa-solid fa-qrcode"></i>
            </span>
            <p class="text-sm text-slate-500">Halo,</p>
            <p class="text-lg font-semibold text-slate-900">{{ $patient->name }}</p>
            <p class="font-mono text-sm text-slate-500">{{ $patient->medical_record_number }}</p>

            @if ($activeQueue)
                <div class="mt-6 rounded-xl border border-blue-100 bg-blue-50 p-4">
                    <p class="text-xs font-medium text-blue-600">Anda sudah memiliki antrean hari ini</p>
                    <p class="text-2xl font-bold text-blue-700">{{ $activeQueue->queue_number }}</p>
                    <p class="mt-1 text-xs text-blue-600">{{ $activeQueue->status_label }}</p>
                </div>
                <a href="{{ route('patient.queue') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700">
                    Lihat Antrean Saya <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            @else
                <form method="POST" action="{{ route('check-in.submit') }}" class="mt-6">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                        <i class="fa-solid fa-ticket"></i> Ambil Antrean
                    </button>
                </form>
            @endif
        </div>
    </div>
@endsection
