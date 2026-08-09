@extends('layouts.guest')

@section('title', 'Verifikasi Data Pasien')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        @if ($errors->any())
            <div class="mb-5 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($patient)
            {{-- Step 2: verified — confirm identity, then create account --}}
            <h2 class="mb-1 text-lg font-semibold text-slate-900">Konfirmasi Data Anda</h2>
            <p class="mb-5 text-sm text-slate-500">Pastikan data berikut sesuai dengan diri Anda.</p>

            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-lg text-emerald-600">
                        <i class="fa-solid fa-circle-check"></i>
                    </span>
                    <div>
                        <p class="text-xs text-emerald-700">Nama</p>
                        <p class="text-sm font-semibold text-slate-900">{{ $patient->name }}</p>
                        <p class="mt-1 text-xs text-emerald-700">No. Rekam Medis</p>
                        <p class="font-mono text-sm font-semibold text-slate-900">{{ $patient->medical_record_number }}</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('register-patient.existing.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        placeholder="nama@email.com">
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Kata Sandi</label>
                    <input type="password" id="password" name="password" required minlength="6"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        placeholder="Minimal 6 karakter">
                </div>
                <div>
                    <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-slate-700">Konfirmasi Kata Sandi</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>

                <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    <i class="fa-solid fa-check"></i> Buat Akun
                </button>
            </form>

            <form method="POST" action="{{ route('register-patient.existing.cancel') }}" class="mt-3">
                @csrf
                <button type="submit" class="w-full text-center text-sm font-medium text-slate-500 hover:text-slate-700">
                    Bukan saya, ulangi verifikasi
                </button>
            </form>
        @else
            {{-- Step 1: verify NIK + phone --}}
            <h2 class="mb-1 text-lg font-semibold text-slate-900">Verifikasi Data Pasien</h2>
            <p class="mb-6 text-sm text-slate-500">Masukkan NIK dan No. HP sesuai data yang terdaftar di klinik.</p>

            <form method="POST" action="{{ route('register-patient.existing.verify') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="nik" class="mb-1.5 block text-sm font-medium text-slate-700">NIK</label>
                    <input type="text" id="nik" name="nik" value="{{ old('nik') }}" required inputmode="numeric" maxlength="16" autofocus
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        placeholder="16 digit NIK">
                </div>
                <div>
                    <label for="phone" class="mb-1.5 block text-sm font-medium text-slate-700">No. HP</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        placeholder="cth. 081234567890">
                </div>

                <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari Data Saya
                </button>
            </form>
        @endif

        <p class="mt-6 text-center text-sm text-slate-500">
            <a href="{{ route('register-patient.choose') }}" class="font-medium text-blue-600 hover:text-blue-700">
                <i class="fa-solid fa-arrow-left text-xs"></i> Kembali
            </a>
        </p>
    </div>
@endsection
