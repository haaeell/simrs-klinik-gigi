@extends('layouts.app')

@section('title', 'Pasien')
@section('page-title', 'Data Pasien')
@section('page-description', 'Kelola data identitas dan rekam medis pasien')

@section('content')
    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="relative w-full sm:max-w-sm">
            <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No RM, NIK, nama, atau No HP..."
                class="w-full rounded-xl border border-slate-300 py-2.5 pl-9 pr-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        </form>

        <a href="{{ route('patients.create') }}"
            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700">
            <i class="fa-solid fa-plus"></i>
            Tambah Pasien
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">No RM</th>
                        <th class="px-5 py-3 font-medium">NIK</th>
                        <th class="px-5 py-3 font-medium">Nama</th>
                        <th class="px-5 py-3 font-medium">Umur</th>
                        <th class="px-5 py-3 font-medium">Jenis Kelamin</th>
                        <th class="px-5 py-3 font-medium">No HP</th>
                        <th class="px-5 py-3 font-medium">Kunjungan Terakhir</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($patients as $patient)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-3.5 font-mono text-xs font-medium text-blue-700">{{ $patient->medical_record_number }}</td>
                            <td class="px-5 py-3.5 text-slate-500">{{ $patient->nik ?: '-' }}</td>
                            <td class="px-5 py-3.5 font-medium text-slate-900">{{ $patient->name }}</td>
                            <td class="px-5 py-3.5 text-slate-500">{{ $patient->birth_date?->age ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-slate-500">{{ $patient->gender_label }}</td>
                            <td class="px-5 py-3.5 text-slate-500">{{ $patient->phone ?: '-' }}</td>
                            <td class="px-5 py-3.5 text-slate-500">
                                {{ $patient->latestVisit?->visit_date?->translatedFormat('d M Y') ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('patients.show', $patient) }}"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-blue-600" title="Detail">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('patients.edit', $patient) }}"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-blue-600" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('queues.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                                        <button type="submit"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-blue-50 hover:text-blue-600" title="Buat Antrean">
                                            <i class="fa-solid fa-ticket"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center">
                                <i class="fa-solid fa-users mb-3 block text-3xl text-slate-300"></i>
                                <p class="text-sm font-medium text-slate-500">
                                    {{ request('search') ? 'Tidak ada pasien yang cocok dengan pencarian.' : 'Belum ada data pasien.' }}
                                </p>
                                <a href="{{ route('patients.create') }}" class="mt-3 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                    <i class="fa-solid fa-plus"></i> Tambah Pasien
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($patients->hasPages())
        <div class="mt-4">{{ $patients->links() }}</div>
    @endif
@endsection
