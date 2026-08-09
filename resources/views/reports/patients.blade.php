@extends('layouts.app')

@section('title', 'Laporan Pasien')
@section('page-title', 'Laporan Pasien')
@section('page-description', 'Rekap seluruh data pasien terdaftar')

@section('content')
    @include('reports._nav')
    @include('reports._print_header', ['title' => 'Laporan Pasien'])

    <form method="GET" class="mb-6 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm print:hidden sm:flex-row sm:items-end">
        <div class="flex-1">
            <label class="mb-1.5 block text-xs font-medium text-slate-600">Cari Pasien</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari No RM atau nama..."
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <i class="fa-solid fa-filter"></i> Tampilkan
            </button>
            <a href="{{ route('reports.patients') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                Reset
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                <i class="fa-solid fa-print"></i> Cetak
            </button>
        </div>
    </form>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 print:grid-cols-2 print:gap-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm print:border print:shadow-none">
            <p class="text-xs text-slate-400">Total Pasien</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $summary['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm print:border print:shadow-none">
            <p class="text-xs text-slate-400">Pasien Baru Bulan Ini</p>
            <p class="mt-1 text-2xl font-semibold text-blue-600">{{ $summary['new_this_month'] }}</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm print:border-0 print:shadow-none">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500 print:bg-transparent">
                    <tr>
                        <th class="px-5 py-3 font-medium">No RM</th>
                        <th class="px-5 py-3 font-medium">NIK</th>
                        <th class="px-5 py-3 font-medium">Nama</th>
                        <th class="px-5 py-3 font-medium">Jenis Kelamin</th>
                        <th class="px-5 py-3 font-medium">Umur</th>
                        <th class="px-5 py-3 font-medium">No HP</th>
                        <th class="px-5 py-3 font-medium">Tanggal Daftar</th>
                        <th class="px-5 py-3 font-medium">Kunjungan Terakhir</th>
                        <th class="px-5 py-3 font-medium">Jumlah Kunjungan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($patients as $patient)
                        <tr>
                            <td class="px-5 py-3 font-mono text-xs text-blue-700">{{ $patient->medical_record_number }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $patient->nik ?: '-' }}</td>
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $patient->name }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $patient->gender_label }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $patient->birth_date?->age ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $patient->phone ?: '-' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $patient->created_at->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $patient->latestVisit?->visit_date?->translatedFormat('d M Y') ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $patient->visits_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-16 text-center">
                                <i class="fa-solid fa-users mb-3 block text-3xl text-slate-300"></i>
                                <p class="text-sm font-medium text-slate-500">Tidak ada data pasien yang cocok.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
