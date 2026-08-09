@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', now()->translatedFormat('l, d F Y'))

@section('content')
    @if (auth()->user()->isAdmin())
        {{-- ADMIN / PETUGAS --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-5">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><i class="fa-solid fa-list-ol"></i></span>
                    <div class="min-w-0">
                        <p class="truncate text-xs text-slate-400">Antrean Hari Ini</p>
                        <p class="text-xl font-semibold text-slate-900">{{ $counts['today'] }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="fa-solid fa-hourglass-half"></i></span>
                    <div class="min-w-0">
                        <p class="truncate text-xs text-slate-400">Menunggu</p>
                        <p class="text-xl font-semibold text-slate-900">{{ $counts['waiting'] }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600"><i class="fa-solid fa-stethoscope"></i></span>
                    <div class="min-w-0">
                        <p class="truncate text-xs text-slate-400">Sedang Diperiksa</p>
                        <p class="text-xl font-semibold text-slate-900">{{ $counts['examining'] }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fa-solid fa-circle-check"></i></span>
                    <div class="min-w-0">
                        <p class="truncate text-xs text-slate-400">Selesai</p>
                        <p class="text-xl font-semibold text-slate-900">{{ $counts['done'] }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600"><i class="fa-solid fa-users"></i></span>
                    <div class="min-w-0">
                        <p class="truncate text-xs text-slate-400">Total Pasien</p>
                        <p class="text-xl font-semibold text-slate-900">{{ $counts['total_patients'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <i class="fa-solid fa-hourglass-half"></i> Rata-rata Waktu Tunggu
                </p>
                <p class="text-2xl font-semibold text-slate-900">{{ $counts['avg_wait_minutes'] }} <span class="text-sm font-normal text-slate-400">menit</span></p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <i class="fa-solid fa-stethoscope"></i> Rata-rata Durasi Pemeriksaan
                </p>
                <p class="text-2xl font-semibold text-slate-900">{{ $counts['avg_examination_minutes'] }} <span class="text-sm font-normal text-slate-400">menit</span></p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <i class="fa-solid fa-user-check"></i> Total Pasien Hari Ini
                </p>
                <p class="text-2xl font-semibold text-slate-900">{{ $counts['patients_today'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    <i class="fa-solid fa-chart-pie"></i> Sumber Pendaftaran
                </p>
                <div class="space-y-1 text-sm">
                    @foreach (\App\Models\Queue::SOURCES as $key => $label)
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">{{ $label }}</span>
                            <span class="font-semibold text-slate-900">{{ $sourceCounts[$key] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
                <h3 class="mb-4 text-sm font-semibold text-slate-900">Tren Kunjungan 7 Hari Terakhir</h3>
                <div class="h-64">
                    <canvas id="chart-visit-trend"></canvas>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold text-slate-900">Sumber Pendaftaran Hari Ini</h3>
                <div class="h-64">
                    <canvas id="chart-source"></canvas>
                </div>
            </div>
        </div>

        <script>
            new Chart(document.getElementById('chart-visit-trend'), {
                type: 'bar',
                data: {
                    labels: @json($visitTrend->pluck('label')),
                    datasets: [{
                        label: 'Antrean',
                        data: @json($visitTrend->pluck('total')),
                        backgroundColor: '#2563eb',
                        borderRadius: 6,
                        maxBarThickness: 40,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                },
            });

            new Chart(document.getElementById('chart-source'), {
                type: 'doughnut',
                data: {
                    labels: @json(collect(\App\Models\Queue::SOURCES)->values()),
                    datasets: [{
                        data: @json(collect(\App\Models\Queue::SOURCES)->keys()->map(fn ($key) => $sourceCounts[$key])),
                        backgroundColor: ['#2563eb', '#7c3aed', '#64748b'],
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                },
            });
        </script>

        <div class="mt-8">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">Antrean Hari Ini</h3>
                <a href="{{ route('queues.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Lihat Semua</a>
            </div>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3 font-medium">No Antrean</th>
                                <th class="px-5 py-3 font-medium">Pasien</th>
                                <th class="px-5 py-3 font-medium">Jam Daftar</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                                <th class="px-5 py-3 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($queueRows as $queue)
                                <tr class="hover:bg-slate-50/60">
                                    <td class="px-5 py-3.5 font-mono text-sm font-semibold text-slate-900">{{ $queue->queue_number }}</td>
                                    <td class="px-5 py-3.5 font-medium text-slate-900">{{ $queue->patient->name }}</td>
                                    <td class="px-5 py-3.5 text-slate-500">{{ $queue->created_at->format('H:i') }}</td>
                                    <td class="px-5 py-3.5">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $queue->status_badge_class }}">{{ $queue->status_label }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        <a href="{{ route('patients.show', $queue->patient) }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-blue-600" title="Lihat Pasien">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-16 text-center">
                                        <i class="fa-solid fa-list-ol mb-3 block text-3xl text-slate-300"></i>
                                        <p class="text-sm font-medium text-slate-500">Belum ada antrean hari ini.</p>
                                        <a href="{{ route('patients.index') }}" class="mt-3 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                            <i class="fa-solid fa-plus"></i> Tambah Antrean
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">Kunjungan Terbaru</h3>
                <a href="{{ route('visits.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Lihat Semua</a>
            </div>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3 font-medium">Pasien</th>
                                <th class="px-5 py-3 font-medium">Dokter</th>
                                <th class="px-5 py-3 font-medium">Tanggal</th>
                                <th class="px-5 py-3 font-medium">Diagnosis</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recentVisits as $visit)
                                <tr class="hover:bg-slate-50/60">
                                    <td class="px-5 py-3.5 font-medium text-slate-900">
                                        <a href="{{ route('visits.show', $visit) }}" class="hover:text-blue-600">{{ $visit->patient->name }}</a>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-500">{{ $visit->doctor->name }}</td>
                                    <td class="px-5 py-3.5 text-slate-500">{{ $visit->visit_date->translatedFormat('d M Y') }}</td>
                                    <td class="px-5 py-3.5 text-slate-500">{{ $visit->diagnosis ?: '-' }}</td>
                                    <td class="px-5 py-3.5">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $visit->status_badge_class }}">{{ $visit->status_label }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-16 text-center">
                                        <i class="fa-solid fa-notes-medical mb-3 block text-3xl text-slate-300"></i>
                                        <p class="text-sm font-medium text-slate-500">Belum ada kunjungan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        {{-- DOKTER --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="fa-solid fa-hourglass-half"></i></span>
                    <div>
                        <p class="text-xs text-slate-400">Antrean Menunggu</p>
                        <p class="text-2xl font-semibold text-slate-900">{{ $counts['waiting'] }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600"><i class="fa-solid fa-stethoscope"></i></span>
                    <div>
                        <p class="text-xs text-slate-400">Sedang Diperiksa</p>
                        <p class="text-2xl font-semibold text-slate-900">{{ $counts['examining'] }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fa-solid fa-circle-check"></i></span>
                    <div>
                        <p class="text-xs text-slate-400">Selesai Hari Ini</p>
                        <p class="text-2xl font-semibold text-slate-900">{{ $counts['done'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <h3 class="mb-4 text-sm font-semibold text-slate-900">Antrean Pasien Berikutnya</h3>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-3 font-medium">No Antrean</th>
                                <th class="px-5 py-3 font-medium">Pasien</th>
                                <th class="px-5 py-3 font-medium">No RM</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                                <th class="px-5 py-3 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($nextQueues as $queue)
                                <tr class="hover:bg-slate-50/60">
                                    <td class="px-5 py-3.5 font-mono text-sm font-semibold text-slate-900">{{ $queue->queue_number }}</td>
                                    <td class="px-5 py-3.5 font-medium text-slate-900">{{ $queue->patient->name }}</td>
                                    <td class="px-5 py-3.5 font-mono text-xs text-slate-500">{{ $queue->patient->medical_record_number }}</td>
                                    <td class="px-5 py-3.5">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $queue->status_badge_class }}">{{ $queue->status_label }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right">
                                        @if ($queue->status === 'called' && (! $queue->room_id || $queue->room->doctor_id === auth()->id()))
                                            <form action="{{ route('queues.start-examination', $queue) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                                    <i class="fa-solid fa-stethoscope"></i> Mulai Pemeriksaan
                                                </button>
                                            </form>
                                        @elseif ($queue->status === 'called')
                                            <span class="text-xs italic text-slate-400">Ruang dokter lain</span>
                                        @else
                                            <span class="text-xs text-slate-400">Menunggu dipanggil</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-16 text-center">
                                        <i class="fa-solid fa-list-ol mb-3 block text-3xl text-slate-300"></i>
                                        <p class="text-sm font-medium text-slate-500">Belum ada antrean pasien hari ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
