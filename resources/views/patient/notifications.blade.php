@extends('layouts.app')

@section('title', 'Notifikasi')
@section('page-title', 'Notifikasi')
@section('page-description', 'Pengingat kontrol dan antrean Anda')

@section('content')
    <div class="space-y-3">
        @forelse ($reminders as $reminder)
            <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4 text-sm font-medium text-amber-700 shadow-sm">
                <i class="fa-solid fa-circle-exclamation mr-1.5"></i> {{ $reminder }}
            </div>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm">
                <i class="fa-solid fa-bell-slash mb-3 block text-3xl text-slate-300"></i>
                <p class="text-sm text-slate-500">Tidak ada notifikasi saat ini.</p>
            </div>
        @endforelse
    </div>
@endsection
