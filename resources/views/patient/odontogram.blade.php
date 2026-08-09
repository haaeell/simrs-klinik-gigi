@extends('layouts.app')

@section('title', 'Odontogram')
@section('page-title', 'Odontogram')
@section('page-description', 'Kondisi gigi Anda')

@section('content')
    @php
        $teethByNumber = $selectedOdontogram?->teeth->keyBy('tooth_number') ?? collect();
        $notableTeeth = $teethByNumber->filter(fn ($t) => $t->condition && $t->condition !== 'Normal')->sortBy('tooth_number');
    @endphp

    @if ($odontograms->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm">
            <i class="fa-solid fa-tooth mb-3 block text-3xl text-slate-300"></i>
            <p class="text-sm text-slate-500">Belum ada data odontogram.</p>
        </div>
    @else
        <div class="space-y-5">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Riwayat Odontogram</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($odontograms as $item)
                        <a href="{{ route('patient.odontogram', ['odontogram_id' => $item->id]) }}"
                            class="inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-medium {{ $selectedOdontogram && $selectedOdontogram->id === $item->id ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            {{ $item->examination_date->translatedFormat('d M Y') }}
                        </a>
                    @endforeach
                </div>
                @if ($odontograms->count() > 1)
                    <a href="{{ route('patient.odontogram.compare') }}" class="mt-3 inline-flex items-center gap-1.5 text-xs font-medium text-blue-600 hover:text-blue-700">
                        <i class="fa-solid fa-code-compare"></i> Bandingkan dengan Pemeriksaan Sebelumnya
                    </a>
                @endif
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                @include('partials.odontogram-grid', [
                    'teethByNumber' => $teethByNumber,
                    'interactive' => false,
                ])
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Kondisi Gigi</p>
                @if ($notableTeeth->isEmpty())
                    <p class="text-sm text-slate-500">Semua gigi dalam kondisi normal.</p>
                @else
                    <div class="grid gap-2.5 sm:grid-cols-2">
                        @foreach ($notableTeeth as $tooth)
                            <div class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                <span class="font-semibold text-slate-900">Gigi {{ $tooth->tooth_number }}</span>
                                <span class="text-slate-500">- {{ $tooth->condition }}{{ $tooth->surfaces ? " ({$tooth->surfaces})" : '' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
@endsection
