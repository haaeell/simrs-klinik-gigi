@extends('layouts.app')

@section('title', 'Tambah Pasien')
@section('page-title', 'Tambah Pasien')
@section('page-description', 'No. Rekam Medis akan dibuat otomatis oleh sistem')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <form method="POST" action="{{ route('patients.store') }}">
                @csrf
                @include('patients._form')

                <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <a href="{{ route('patients.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                        <i class="fa-solid fa-check"></i> Simpan Pasien
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
