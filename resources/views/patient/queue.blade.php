@extends('layouts.app')

@section('title', 'Antrean Saya')
@section('page-title', 'Antrean Saya')
@section('page-description', now()->translatedFormat('l, d F Y'))

@section('content')
    <div class="mx-auto max-w-xl">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm sm:p-8">
            @if ($activeQueue)
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nomor Antrean Anda</p>
                <p id="qs-number" class="my-3 text-5xl font-bold text-blue-600">{{ $activeQueue->queue_number }}</p>
                <span id="qs-badge" class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $activeQueue->status_badge_class }}">
                    {{ $activeQueue->status_label }}
                </span>

                <p id="qs-alert" class="mt-4 hidden rounded-xl px-4 py-3 text-sm font-medium"></p>

                <div class="mt-6 grid grid-cols-2 gap-4 border-t border-slate-100 pt-5 text-left sm:grid-cols-4">
                    <div>
                        <p class="text-xs text-slate-400">Jam Daftar</p>
                        <p class="text-sm font-semibold text-slate-800">{{ $activeQueue->created_at->format('H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Sumber</p>
                        <p class="text-sm font-semibold text-slate-800">{{ $activeQueue->source_label }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Pasien di Depan</p>
                        <p id="qs-ahead" class="text-sm font-semibold text-slate-800">{{ $activeQueue->patientsAhead() }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Estimasi Tunggu</p>
                        <p id="qs-estimate" class="text-sm font-semibold text-slate-800">
                            {{ $activeQueue->status === 'waiting' ? '±'.$activeQueue->estimatedWaitMinutes().' menit' : '-' }}
                        </p>
                    </div>
                </div>
            @else
                <i class="fa-solid fa-ticket mb-3 block text-3xl text-slate-300"></i>
                <p class="mb-5 text-sm text-slate-500">Anda belum memiliki antrean hari ini.</p>
                <form method="POST" action="{{ route('patient.queue.take') }}" class="mx-auto max-w-sm text-left">
                    @csrf
                    @if ($activeRooms->isNotEmpty())
                        <div class="mb-4">
                            <label for="room_id" class="mb-1.5 block text-sm font-medium text-slate-700">Pilih Dokter</label>
                            <select id="room_id" name="room_id"
                                class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                <option value="">(Belum tahu / bebas)</option>
                                @foreach ($activeRooms as $room)
                                    <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                        {{ $room->doctor->name }} &mdash; {{ $room->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('room_id')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div class="mb-5">
                        <label for="complaint" class="mb-1.5 block text-sm font-medium text-slate-700">Keluhan</label>
                        <textarea id="complaint" name="complaint" rows="3" placeholder="cth. Gigi sakit ketika mengunyah"
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">{{ old('complaint') }}</textarea>
                        @error('complaint')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" title="Ambil nomor antrean dengan dokter dan keluhan di atas"
                        class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                        <i class="fa-solid fa-ticket"></i> Ambil Antrean
                    </button>
                </form>
            @endif
        </div>
    </div>
@endsection

@if ($activeQueue)
    @push('scripts')
        <script>
            (function () {
                function renderQueueStatus(data) {
                    if (!data.active) {
                        window.location.reload();
                        return;
                    }

                    document.getElementById('qs-number').textContent = data.queue_number;

                    const badge = document.getElementById('qs-badge');
                    badge.className = 'inline-flex items-center rounded-full px-3 py-1 text-sm font-medium ' + data.status_badge_class;
                    badge.textContent = data.status_label;

                    document.getElementById('qs-ahead').textContent = data.ahead ?? '-';
                    document.getElementById('qs-estimate').textContent = (data.status === 'waiting' && data.estimate_minutes !== null)
                        ? ('±' + data.estimate_minutes + ' menit') : '-';

                    const alertEl = document.getElementById('qs-alert');
                    if (data.status === 'called') {
                        alertEl.textContent = 'NOMOR ANDA SEDANG DIPANGGIL.';
                        alertEl.className = 'mt-4 rounded-xl bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700';
                    } else if (data.status === 'waiting' && data.ahead !== null && data.ahead <= 2) {
                        alertEl.textContent = 'Antrean Anda sudah dekat. Silakan bersiap.';
                        alertEl.className = 'mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm font-medium text-amber-700';
                    } else {
                        alertEl.className = 'mt-4 hidden';
                    }
                }

                async function pollQueueStatus() {
                    try {
                        const response = await fetch('{{ route('patient.queue.status') }}', { headers: { Accept: 'application/json' } });
                        renderQueueStatus(await response.json());
                    } catch (error) {
                        // Silent — the next interval will retry.
                    }
                }

                setInterval(pollQueueStatus, 5000);
            })();
        </script>
    @endpush
@endif
