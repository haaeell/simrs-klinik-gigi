@extends('layouts.guest')

@section('title', 'Daftar Pasien Baru')
@section('container-class', 'max-w-2xl')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <h2 class="mb-1 text-lg font-semibold text-slate-900">Daftar Pasien Baru</h2>
        <p class="mb-6 text-sm text-slate-500">No. Rekam Medis akan dibuat otomatis oleh sistem.</p>

        @if ($errors->any())
            <div class="mb-5 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('register-patient.new.store') }}" class="space-y-6">
            @csrf

            <div>
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Data Diri</p>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="mb-1.5 block text-sm font-medium text-slate-700">Nama Lengkap</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            placeholder="cth. Haikal Ramadhan">
                    </div>
                    <div>
                        <label for="nik" class="mb-1.5 block text-sm font-medium text-slate-700">NIK</label>
                        <input type="text" id="nik" name="nik" value="{{ old('nik') }}" inputmode="numeric" maxlength="16"
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            placeholder="16 digit NIK (opsional)">
                    </div>
                    <div>
                        <label for="phone" class="mb-1.5 block text-sm font-medium text-slate-700">No. HP</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            placeholder="cth. 081234567890">
                    </div>
                    <div>
                        <label for="birth_place" class="mb-1.5 block text-sm font-medium text-slate-700">Tempat Lahir</label>
                        <input type="text" id="birth_place" name="birth_place" value="{{ old('birth_place') }}"
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label for="birth_date" class="mb-1.5 block text-sm font-medium text-slate-700">Tanggal Lahir</label>
                        <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date') }}" max="{{ date('Y-m-d') }}"
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label for="gender" class="mb-1.5 block text-sm font-medium text-slate-700">Jenis Kelamin</label>
                        <select id="gender" name="gender"
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            <option value="">Pilih jenis kelamin</option>
                            <option value="L" {{ old('gender') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('gender') === 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label for="occupation" class="mb-1.5 block text-sm font-medium text-slate-700">Pekerjaan</label>
                        <input type="text" id="occupation" name="occupation" value="{{ old('occupation') }}"
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="address" class="mb-1.5 block text-sm font-medium text-slate-700">Alamat</label>
                        <textarea id="address" name="address" rows="2"
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">{{ old('address') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-5">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Akun Login</p>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
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
                </div>
            </div>

            <button type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                <i class="fa-solid fa-user-plus"></i> Daftar
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500">
            <a href="{{ route('register-patient.choose') }}" class="font-medium text-blue-600 hover:text-blue-700">
                <i class="fa-solid fa-arrow-left text-xs"></i> Kembali
            </a>
        </p>
    </div>
@endsection
