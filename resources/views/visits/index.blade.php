@extends('layouts.app')

@section('title', 'Rekam Medis')
@section('page-title', 'Rekam Medis')
@section('page-description', 'Riwayat seluruh kunjungan dan pemeriksaan pasien')

@section('content')
    <div class="mb-5">
        <form method="GET" data-live-search class="relative w-full sm:max-w-sm">
            <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No RM atau nama pasien..." title="Ketik lalu tunggu sebentar, hasil otomatis terfilter"
                class="w-full rounded-xl border border-slate-300 py-2.5 pl-9 pr-16 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            <button type="submit" title="Cari sekarang" class="absolute right-1.5 top-1/2 -translate-y-1/2 rounded-lg px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50">
                Cari
            </button>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Tanggal</th>
                        <th class="px-5 py-3 font-medium">No RM</th>
                        <th class="px-5 py-3 font-medium">Pasien</th>
                        <th class="px-5 py-3 font-medium">Dokter</th>
                        <th class="px-5 py-3 font-medium">Keluhan</th>
                        <th class="px-5 py-3 font-medium">Diagnosis</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($visits as $visit)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-3.5 text-slate-500">{{ $visit->visit_date->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-3.5 font-mono text-xs font-medium text-blue-700">{{ $visit->patient->medical_record_number }}</td>
                            <td class="px-5 py-3.5 font-medium text-slate-900">{{ $visit->patient->name }}</td>
                            <td class="px-5 py-3.5 text-slate-500">{{ $visit->doctor->name }}</td>
                            <td class="max-w-[200px] truncate px-5 py-3.5 text-slate-500">{{ $visit->complaint ?: '-' }}</td>
                            <td class="max-w-[200px] truncate px-5 py-3.5 text-slate-500">{{ $visit->diagnosis ?: '-' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $visit->status_badge_class }}">
                                    {{ $visit->status_label }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('visits.show', $visit) }}"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-blue-600" title="Lihat Detail">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center">
                                <i class="fa-solid fa-notes-medical mb-3 block text-3xl text-slate-300"></i>
                                <p class="text-sm font-medium text-slate-500">
                                    {{ request('search') ? 'Tidak ada rekam medis yang cocok dengan pencarian.' : 'Belum ada riwayat pemeriksaan.' }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($visits->hasPages())
        <div class="mt-4">{{ $visits->links() }}</div>
    @endif
@endsection
