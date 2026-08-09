@extends('layouts.app')

@section('title', 'Antrean')
@section('page-title', 'Antrean Hari Ini')
@section('page-description', now()->translatedFormat('l, d F Y'))

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="grid flex-1 grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs text-slate-400">Menunggu</p>
                <p data-count="waiting" class="mt-1 text-2xl font-semibold text-amber-600">{{ $counts['waiting'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs text-slate-400">Dipanggil</p>
                <p data-count="called" class="mt-1 text-2xl font-semibold text-blue-600">{{ $counts['called'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs text-slate-400">Sedang Diperiksa</p>
                <p data-count="examining" class="mt-1 text-2xl font-semibold text-violet-600">{{ $counts['examining'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs text-slate-400">Selesai</p>
                <p data-count="done" class="mt-1 text-2xl font-semibold text-emerald-600">{{ $counts['done'] }}</p>
            </div>
        </div>
    </div>

    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-sm font-semibold text-slate-900">Daftar Antrean</h2>
        <div class="flex items-center gap-2">
            <a href="{{ route('queues.display') }}" target="_blank"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">
                <i class="fa-solid fa-tv"></i> Layar Antrean
            </a>
            @if (auth()->user()->isAdmin() && \Illuminate\Support\Facades\Route::has('queues.display-settings.edit'))
                <a href="{{ route('queues.display-settings.edit') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">
                    <i class="fa-solid fa-gear"></i> Atur Suara
                </a>
            @endif
            <a href="{{ route('patients.index') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                <i class="fa-solid fa-plus"></i> Tambah Antrean
            </a>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Nomor Antrean</th>
                        <th class="px-5 py-3 font-medium">Pasien</th>
                        <th class="px-5 py-3 font-medium">No RM</th>
                        <th class="px-5 py-3 font-medium">Sumber</th>
                        <th class="px-5 py-3 font-medium">Estimasi Tunggu</th>
                        <th class="px-5 py-3 font-medium">Jam Daftar</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($queues as $queue)
                        <tr data-queue-row="{{ $queue->id }}" class="hover:bg-slate-50/60">
                            <td class="px-5 py-3.5 font-mono text-sm font-semibold text-slate-900">{{ $queue->queue_number }}</td>
                            <td class="px-5 py-3.5">
                                <a href="{{ route('patients.show', $queue->patient) }}" class="font-medium text-slate-900 hover:text-blue-600">
                                    {{ $queue->patient->name }}
                                </a>
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-slate-500">{{ $queue->patient->medical_record_number }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $queue->source_badge_class }}">
                                    {{ $queue->source_label }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-500">
                                {{ $queue->status === 'waiting' ? '±'.($queue->patientsAhead() * $avgExaminationMinutes).' menit' : '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-500">{{ $queue->created_at->format('H:i') }}</td>
                            <td class="px-5 py-3.5">
                                <span data-status-badge class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $queue->status_badge_class }}">
                                    {{ $queue->status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-2">
                                    <div data-status-actions="waiting" class="flex items-center gap-2 {{ $queue->status !== 'waiting' ? 'hidden' : '' }}">
                                        <button type="button" data-queue-action data-url="{{ route('queues.call', $queue) }}"
                                            class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                            <i class="fa-solid fa-volume-high"></i> Panggil
                                        </button>
                                        <button type="button" data-queue-action data-url="{{ route('queues.skip', $queue) }}"
                                            data-confirm-text="Antrean {{ $queue->queue_number }} akan dilewati dan dapat dipanggil kembali nanti."
                                            class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
                                            Lewati
                                        </button>
                                    </div>
                                    <div data-status-actions="called" class="flex items-center gap-2 {{ $queue->status !== 'called' ? 'hidden' : '' }}">
                                        <button type="button" data-queue-action data-url="{{ route('queues.recall', $queue) }}"
                                            class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100">
                                            <i class="fa-solid fa-rotate-right"></i> Panggil Ulang
                                        </button>
                                        @if (auth()->user()->isDokter())
                                            <form action="{{ route('queues.start-examination', $queue) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                                    <i class="fa-solid fa-stethoscope"></i> Mulai Pemeriksaan
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                    <div data-status-actions="skipped" class="flex items-center gap-2 {{ $queue->status !== 'skipped' ? 'hidden' : '' }}">
                                        <button type="button" data-queue-action data-url="{{ route('queues.call', $queue) }}"
                                            class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700">
                                            <i class="fa-solid fa-volume-high"></i> Panggil
                                        </button>
                                    </div>
                                    <div data-status-actions="examining" class="{{ $queue->status !== 'examining' ? 'hidden' : '' }} text-xs italic text-slate-400">
                                        Sedang diperiksa
                                    </div>
                                    <div data-status-actions="done" class="{{ $queue->status !== 'done' ? 'hidden' : '' }} text-xs text-slate-300">
                                        &mdash;
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center">
                                <i class="fa-solid fa-list-ol mb-3 block text-3xl text-slate-300"></i>
                                <p class="text-sm font-medium text-slate-500">Belum ada antrean hari ini.</p>
                                <a href="{{ route('patients.index') }}" class="mt-3 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                    <i class="fa-solid fa-plus"></i> Tambah Antrean
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
