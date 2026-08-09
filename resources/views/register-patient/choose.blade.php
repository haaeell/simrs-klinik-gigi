@extends('layouts.guest')

@section('title', 'Daftar Akun Pasien')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <h2 class="mb-1 text-lg font-semibold text-slate-900">Daftar Akun Pasien</h2>
        <p class="mb-6 text-sm text-slate-500">Pilih salah satu untuk melanjutkan.</p>

        <div class="space-y-3">
            <a href="{{ route('register-patient.existing') }}"
                class="flex items-center gap-4 rounded-xl border border-slate-200 p-4 transition-colors hover:border-blue-300 hover:bg-blue-50/50">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-lg text-blue-600">
                    <i class="fa-solid fa-user-check"></i>
                </span>
                <span class="min-w-0">
                    <span class="block text-sm font-semibold text-slate-900">Saya sudah pernah berobat</span>
                    <span class="block text-xs text-slate-500">Sudah memiliki data rekam medis di klinik</span>
                </span>
                <i class="fa-solid fa-chevron-right ml-auto shrink-0 text-slate-300"></i>
            </a>

            <a href="{{ route('register-patient.new') }}"
                class="flex items-center gap-4 rounded-xl border border-slate-200 p-4 transition-colors hover:border-blue-300 hover:bg-blue-50/50">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-lg text-emerald-600">
                    <i class="fa-solid fa-user-plus"></i>
                </span>
                <span class="min-w-0">
                    <span class="block text-sm font-semibold text-slate-900">Saya pasien baru</span>
                    <span class="block text-xs text-slate-500">Belum pernah berobat di klinik ini</span>
                </span>
                <i class="fa-solid fa-chevron-right ml-auto shrink-0 text-slate-300"></i>
            </a>
        </div>

        <p class="mt-6 text-center text-sm text-slate-500">
            Sudah punya akun? <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-700">Masuk</a>
        </p>
    </div>
@endsection
