@extends('layouts.app')

@section('title', 'Profil')
@section('page-title', 'Profil Saya')
@section('page-description', 'Kelola data akun Anda')

@section('content')
    <div class="mx-auto max-w-2xl space-y-5">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-slate-900">Data Diri</h3>
            <p class="mb-4 text-xs text-slate-400">Untuk mengubah NIK, nama, atau tanggal lahir, silakan hubungi petugas klinik.</p>
            <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs text-slate-400">No. Rekam Medis</dt>
                    <dd class="mt-0.5 font-mono text-sm font-medium text-slate-800">{{ $patient->medical_record_number }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">NIK</dt>
                    <dd class="mt-0.5 text-sm font-medium text-slate-800">{{ $patient->nik ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Nama</dt>
                    <dd class="mt-0.5 text-sm font-medium text-slate-800">{{ $patient->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Tanggal Lahir</dt>
                    <dd class="mt-0.5 text-sm font-medium text-slate-800">{{ $patient->birth_date?->translatedFormat('d F Y') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-400">Jenis Kelamin</dt>
                    <dd class="mt-0.5 text-sm font-medium text-slate-800">{{ $patient->gender_label }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-slate-900">Ubah Data</h3>

            <form method="POST" action="{{ route('patient.profile.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="address" class="mb-1.5 block text-sm font-medium text-slate-700">Alamat</label>
                    <textarea id="address" name="address" rows="2"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">{{ old('address', $patient->address) }}</textarea>
                </div>
                <div>
                    <label for="phone" class="mb-1.5 block text-sm font-medium text-slate-700">No. HP</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $patient->phone) }}"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Kata Sandi Baru <span class="font-normal text-slate-400">(opsional)</span></label>
                        <input type="password" id="password" name="password" minlength="6"
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            placeholder="Kosongkan jika tidak diubah">
                    </div>
                    <div>
                        <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-slate-700">Konfirmasi Kata Sandi</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" minlength="6"
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>

                <div class="flex justify-end border-t border-slate-100 pt-5">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
