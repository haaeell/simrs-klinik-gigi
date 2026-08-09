@extends('layouts.app')

@section('title', 'Laporan Tindakan')
@section('page-title', 'Laporan Tindakan')
@section('page-description', 'Rekap tindakan / perawatan berdasarkan periode')

@section('content')
    @include('reports._nav')

    @php
        $periode = \Illuminate\Support\Carbon::parse($start)->translatedFormat('d F Y') . ' - ' . \Illuminate\Support\Carbon::parse($end)->translatedFormat('d F Y');
    @endphp
    @include('reports._print_header', ['title' => 'Laporan Tindakan', 'periode' => $periode])

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
        <div>
            <label class="mb-1.5 block text-xs font-medium text-slate-600">Dokter</label>
            <select name="doctor_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="">Semua Dokter</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" {{ (string) $doctorId === (string) $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-medium text-slate-600">Kata Kunci Tindakan</label>
            <input type="text" name="keyword" value="{{ $keyword }}" placeholder="cth. Penambalan"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        </div>
        <div class="flex items-end gap-2 sm:col-span-4">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <i class="fa-solid fa-filter"></i> Tampilkan
            </button>
            <a href="{{ route('reports.treatments') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                Reset
            </a>
            <button type="button" onclick="window.print()" class="ml-auto inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                <i class="fa-solid fa-print"></i> Cetak
            </button>
        </div>
    </form>

    <p class="mb-4 text-sm text-slate-500">Total tindakan: <span class="font-semibold text-slate-900">{{ $treatments->count() }}</span></p>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm print:border-0 print:shadow-none">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500 print:bg-transparent">
                    <tr>
                        <th class="px-5 py-3 font-medium">Tanggal</th>
                        <th class="px-5 py-3 font-medium">No RM</th>
                        <th class="px-5 py-3 font-medium">Pasien</th>
                        <th class="px-5 py-3 font-medium">Gigi</th>
                        <th class="px-5 py-3 font-medium">Diagnosis</th>
                        <th class="px-5 py-3 font-medium">ICD-10</th>
                        <th class="px-5 py-3 font-medium">Tindakan</th>
                        <th class="px-5 py-3 font-medium">Dokter</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($treatments as $treatment)
                        <tr>
                            <td class="px-5 py-3 text-slate-500">{{ $treatment->visit->visit_date->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-3 font-mono text-xs text-blue-700">{{ $treatment->visit->patient->medical_record_number }}</td>
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $treatment->visit->patient->name }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $treatment->tooth_number ?: '-' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $treatment->diagnosis ?: '-' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $treatment->icd10_code ?: '-' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $treatment->treatment }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $treatment->visit->doctor->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center">
                                <i class="fa-solid fa-notes-medical mb-3 block text-3xl text-slate-300"></i>
                                <p class="text-sm font-medium text-slate-500">Tidak ada tindakan pada periode ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
