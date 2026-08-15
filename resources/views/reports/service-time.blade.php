@extends('layouts.app')

@section('title', 'Laporan Waktu Pelayanan')
@section('page-title', 'Laporan Waktu Pelayanan')
@section('page-description', 'Rekap waktu tunggu dan durasi pemeriksaan pasien')

@section('content')
    @include('reports._nav')

    @php
        $periode = \Illuminate\Support\Carbon::parse($start)->translatedFormat('d F Y') . ' - ' . \Illuminate\Support\Carbon::parse($end)->translatedFormat('d F Y');
    @endphp
    @include('reports._print_header', ['title' => 'Laporan Waktu Pelayanan', 'periode' => $periode])

    <form method="GET" class="mb-6 grid grid-cols-1 gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm print:hidden sm:grid-cols-5">
        <div>
            <label class="mb-1.5 block text-xs font-medium text-slate-600">Tanggal Mulai</label>
            <input type="date" name="start_date" value="{{ $start }}"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-medium text-slate-600">Tanggal Akhir</label>
            <input type="date" name="end_date" value="{{ $end }}"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-medium text-slate-600">Dokter</label>
            <select name="doctor_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="">Semua Dokter</option>
                @foreach ($doctors as $doctor)
                    <option value="{{ $doctor->id }}" {{ (string) $doctorId === (string) $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1.5 block text-xs font-medium text-slate-600">Sumber</label>
            <select name="source" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                <option value="">Semua Sumber</option>
                @foreach (\App\Models\Queue::SOURCES as $key => $label)
                    <option value="{{ $key }}" {{ $source === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <i class="fa-solid fa-filter"></i> Tampilkan
            </button>
            <button type="button" onclick="window.print()" class="ml-auto inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                <i class="fa-solid fa-print"></i> Cetak
            </button>
        </div>
    </form>

    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4 print:grid-cols-4 print:gap-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm print:border print:shadow-none">
            <p class="text-xs text-slate-400">Total Antrean</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $summary['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm print:border print:shadow-none">
            <p class="text-xs text-slate-400">Rata-rata Waktu Tunggu</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $summary['avg_wait_minutes'] }} <span class="text-sm font-normal text-slate-400">menit</span></p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm print:border print:shadow-none">
            <p class="text-xs text-slate-400">Rata-rata Durasi Pemeriksaan</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $summary['avg_examination_minutes'] }} <span class="text-sm font-normal text-slate-400">menit</span></p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm print:border print:shadow-none">
            <p class="mb-1 text-xs text-slate-400">Sumber Pendaftaran</p>
            <div class="space-y-0.5 text-xs">
                @foreach (\App\Models\Queue::SOURCES as $key => $label)
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">{{ $label }}</span>
                        <span class="font-semibold text-slate-900">{{ $summary['source_counts'][$key] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm print:hidden">
        <h3 class="mb-4 text-sm font-semibold text-slate-900">Rata-rata Waktu Tunggu &amp; Durasi Pemeriksaan per Hari</h3>
        <div class="h-64">
            <canvas id="chart-service-time"></canvas>
        </div>
    </div>
    <script>
        new Chart(document.getElementById('chart-service-time'), {
            type: 'line',
            data: {
                labels: @json($dailyServiceTime->pluck('label')),
                datasets: [
                    {
                        label: 'Waktu Tunggu (menit)',
                        data: @json($dailyServiceTime->pluck('wait')),
                        borderColor: '#2563eb',
                        backgroundColor: '#2563eb',
                        tension: 0.3,
                    },
                    {
                        label: 'Durasi Pemeriksaan (menit)',
                        data: @json($dailyServiceTime->pluck('examination')),
                        borderColor: '#7c3aed',
                        backgroundColor: '#7c3aed',
                        tension: 0.3,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            },
        });
    </script>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm print:border-0 print:shadow-none">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500 print:bg-transparent">
                    <tr>
                        <th class="px-5 py-3 font-medium">No</th>
                        <th class="px-5 py-3 font-medium">Tanggal</th>
                        <th class="px-5 py-3 font-medium">No Antrean</th>
                        <th class="px-5 py-3 font-medium">Pasien</th>
                        <th class="px-5 py-3 font-medium">Sumber</th>
                        <th class="px-5 py-3 font-medium">Jam Daftar</th>
                        <th class="px-5 py-3 font-medium">Jam Mulai</th>
                        <th class="px-5 py-3 font-medium">Jam Selesai</th>
                        <th class="px-5 py-3 font-medium">Waktu Tunggu</th>
                        <th class="px-5 py-3 font-medium">Durasi Pemeriksaan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($queues as $queue)
                        <tr>
                            <td class="px-5 py-3 text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $queue->queue_date->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-3 font-mono text-sm font-semibold text-slate-900">{{ $queue->queue_number }}</td>
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $queue->patient->name }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $queue->source_badge_class }} print:px-0 print:text-slate-700">
                                    {{ $queue->source_label }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $queue->created_at->format('H:i') }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $queue->started_at?->format('H:i') ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $queue->finished_at?->format('H:i') ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $queue->called_at ? max(0, (int) round($queue->created_at->diffInMinutes($queue->called_at))) . ' menit' : '-' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $queue->started_at && $queue->finished_at ? max(0, (int) round($queue->started_at->diffInMinutes($queue->finished_at))) . ' menit' : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-16 text-center">
                                <i class="fa-solid fa-clock mb-3 block text-3xl text-slate-300"></i>
                                <p class="text-sm font-medium text-slate-500">Tidak ada data pada periode ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
