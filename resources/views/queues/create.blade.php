@extends('layouts.app')

@section('title', 'Buat Antrean')
@section('page-title', 'Buat Antrean')
@section('page-description', 'Pilih dokter dan catat keluhan sebelum mencetak nomor antrean')

@section('content')
    <div class="mb-4">
        <a href="{{ route('patients.show', $patient) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-700">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Detail Pasien
        </a>
    </div>

    <div class="mx-auto max-w-2xl">
        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-lg font-semibold text-blue-600">
                    {{ strtoupper(substr($patient->name, 0, 1)) }}
                </span>
                <div class="min-w-0">
                    <p class="truncate font-semibold text-slate-900">{{ $patient->name }}</p>
                    <p class="text-xs text-slate-500">
                        {{ $patient->medical_record_number }}
                        &middot; {{ $patient->gender_label }}
                        @if ($patient->birth_date?->age !== null) &middot; {{ $patient->birth_date->age }} tahun @endif
                        &middot; {{ $patient->phone ?: 'No. HP belum diisi' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <form method="POST" action="{{ route('queues.store') }}">
                @csrf
                <input type="hidden" name="patient_id" value="{{ $patient->id }}">

                <div class="space-y-5">
                    @if ($activeRooms->isNotEmpty())
                        <div>
                            <label for="room_id" class="mb-1.5 block text-sm font-medium text-slate-700">Dokter Tujuan</label>
                            <select id="room_id" name="room_id"
                                class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                <option value="">(Belum ditentukan &mdash; pilih saat memanggil)</option>
                                @foreach ($activeRooms as $room)
                                    <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                        {{ $room->doctor->name }} &mdash; {{ $room->name }} ({{ $room->todayStatusLabel() }})
                                    </option>
                                @endforeach
                            </select>
                            @error('room_id')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-700">
                            <i class="fa-solid fa-circle-info mr-1"></i>
                            Belum ada ruangan/dokter aktif. Antrean tetap bisa dibuat, ruangan bisa ditentukan nanti saat memanggil.
                            Atur di menu <span class="font-semibold">Ruangan</span>.
                        </div>
                    @endif

                    <div>
                        <label for="complaint" class="mb-1.5 block text-sm font-medium text-slate-700">Keluhan Awal</label>
                        <textarea id="complaint" name="complaint" rows="3" placeholder="cth. Gigi sakit ketika mengunyah sejak 2 hari lalu"
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">{{ old('complaint') }}</textarea>
                        <p class="mt-1.5 text-xs text-slate-400">Akan tercatat sebagai keluhan awal di rekam medis dan bisa dilengkapi lagi oleh dokter.</p>
                        @error('complaint')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <a href="{{ route('patients.show', $patient) }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
                    <button type="submit" title="Simpan antrean dan cetak nomor"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                        <i class="fa-solid fa-ticket"></i> Buat Antrean &amp; Cetak Nomor
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
