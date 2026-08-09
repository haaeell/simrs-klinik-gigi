@extends('layouts.app')

@section('title', 'Edit Pasien')
@section('page-title', 'Edit Data Pasien')
@section('page-description', $patient->medical_record_number . ' — ' . $patient->name)

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <form method="POST" action="{{ route('patients.update', $patient) }}">
                @csrf
                @method('PUT')
                @include('patients._form')

                <div class="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-5">
                    <a href="{{ route('patients.show', $patient) }}" class="rounded-xl px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                        <i class="fa-solid fa-check"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
