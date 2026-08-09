@extends('layouts.app')

@section('title', 'Laporan Antrean')
@section('page-title', 'Laporan Antrean')
@section('page-description', 'Rekap antrean pasien berdasarkan periode')

@section('content')
    @include('reports._nav')

    @php
        $periode = \Illuminate\Support\Carbon::parse($start)->translatedFormat('d F Y') . ' - ' . \Illuminate\Support\Carbon::parse($end)->translatedFormat('d F Y');
    @endphp
    @include('reports._print_header', ['title' => 'Laporan Antrean', 'periode' => $periode])

    <form method="GET" class="mb-6 grid grid-cols-1 gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm print:hidden sm:grid-cols-4">
        <div>
            <label class="mb-1.5 block text-xs font-medium text-slate-600">Tanggal Mulai</label>
            <input type="date" name="start_date" value="{{ $start }}"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-medium text-slate-600">Tanggal Akhir</label>
            <input type="date" name="end_date" value="{{ $end }}"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        </div>
        <div class="flex items-end gap-2 sm:col-span-2">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <i class="fa-solid fa-filter"></i> Tampilkan
            </button>
            <a href="{{ route('reports.queues') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                Reset
            </a>
            <button type="button" onclick="window.print()" class="ml-auto inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                <i class="fa-solid fa-print"></i> Cetak
            </button>
        </div>
    </form>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3 print:grid-cols-3 print:gap-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm print:border print:shadow-none">
            <p class="text-xs text-slate-400">Total Antrean</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $summary['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm print:border print:shadow-none">
            <p class="text-xs text-slate-400">Selesai</p>
            <p class="mt-1 text-2xl font-semibold text-emerald-600">{{ $summary['done'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm print:border print:shadow-none">
            <p class="text-xs text-slate-400">Dilewati</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $summary['skipped'] }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm print:border-0 print:shadow-none">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500 print:bg-transparent">
                    <tr>
                        <th class="px-5 py-3 font-medium">Tanggal</th>
                        <th class="px-5 py-3 font-medium">Nomor Antrean</th>
                        <th class="px-5 py-3 font-medium">No RM</th>
                        <th class="px-5 py-3 font-medium">Pasien</th>
                        <th class="px-5 py-3 font-medium">Jam Daftar</th>
                        <th class="px-5 py-3 font-medium">Jam Dipanggil</th>
                        <th class="px-5 py-3 font-medium">Jam Selesai</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($queues as $queue)
                        <tr>
                            <td class="px-5 py-3 text-slate-500">{{ $queue->queue_date->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-3 font-mono text-sm font-semibold text-slate-900">{{ $queue->queue_number }}</td>
                            <td class="px-5 py-3 font-mono text-xs text-blue-700">{{ $queue->patient->medical_record_number }}</td>
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $queue->patient->name }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $queue->created_at->format('H:i') }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $queue->called_at?->format('H:i') ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $queue->finished_at?->format('H:i') ?? '-' }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $queue->status_badge_class }} print:px-0 print:text-slate-700">
                                    {{ $queue->status_label }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center">
                                <i class="fa-solid fa-list-ol mb-3 block text-3xl text-slate-300"></i>
                                <p class="text-sm font-medium text-slate-500">Tidak ada antrean pada periode ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
