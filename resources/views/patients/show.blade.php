@extends('layouts.app')

@section('title', $patient->name)
@section('page-title', 'Detail Pasien')
@section('page-description', $patient->medical_record_number)

@section('content')
    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-4">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-xl font-semibold text-blue-600">
                    {{ strtoupper(substr($patient->name, 0, 1)) }}
                </span>
                <div>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 font-mono text-xs font-medium text-slate-600">
                        {{ $patient->medical_record_number }}
                    </span>
                    <h2 class="mt-1.5 text-lg font-semibold text-slate-900">{{ $patient->name }}</h2>
                    <p class="text-sm text-slate-500">{{ $patient->nik ?: 'NIK belum diisi' }}</p>
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <a href="{{ route('patients.edit', $patient) }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                    <i class="fa-solid fa-pen"></i> Edit Data
                </a>
                <form action="{{ route('queues.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                        <i class="fa-solid fa-ticket"></i> Buat Antrean
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-2 gap-4 border-t border-slate-100 pt-5 sm:grid-cols-4">
            <div>
                <p class="text-xs text-slate-400">Umur</p>
                <p class="text-sm font-medium text-slate-800">{{ $patient->birth_date?->age !== null ? $patient->birth_date->age.' tahun' : '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Jenis Kelamin</p>
                <p class="text-sm font-medium text-slate-800">{{ $patient->gender_label }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">No. HP</p>
                <p class="text-sm font-medium text-slate-800">{{ $patient->phone ?: '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Terdaftar</p>
                <p class="text-sm font-medium text-slate-800">{{ $patient->created_at->translatedFormat('d F Y') }}</p>
            </div>
        </div>
    </div>

    <div data-tabs>
        <div class="mb-5 flex gap-1 overflow-x-auto border-b border-slate-200">
            <button type="button" data-tab-target="identitas" class="flex shrink-0 items-center gap-2 rounded-t-lg px-4 py-2.5 text-sm font-medium">
                <i class="fa-solid fa-id-card"></i> Identitas
            </button>
            <button type="button" data-tab-target="medis" class="flex shrink-0 items-center gap-2 rounded-t-lg px-4 py-2.5 text-sm font-medium">
                <i class="fa-solid fa-notes-medical"></i> Data Medis
            </button>
            <button type="button" data-tab-target="odontogram" class="flex shrink-0 items-center gap-2 rounded-t-lg px-4 py-2.5 text-sm font-medium">
                <i class="fa-solid fa-tooth"></i> Odontogram
            </button>
            <button type="button" data-tab-target="riwayat" class="flex shrink-0 items-center gap-2 rounded-t-lg px-4 py-2.5 text-sm font-medium">
                <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Perawatan
            </button>
            <button type="button" data-tab-target="lampiran" class="flex shrink-0 items-center gap-2 rounded-t-lg px-4 py-2.5 text-sm font-medium">
                <i class="fa-solid fa-paperclip"></i> Lampiran
            </button>
        </div>

        {{-- Identitas --}}
        <div data-tab-panel="identitas">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <h3 class="mb-5 text-sm font-semibold text-slate-900">Informasi Identitas</h3>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-slate-400">Nama Lengkap</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">{{ $patient->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">NIK</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">{{ $patient->nik ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Tempat, Tanggal Lahir</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">
                            {{ $patient->birth_place ?: '-' }}, {{ $patient->birth_date?->translatedFormat('d F Y') ?? '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Jenis Kelamin</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">{{ $patient->gender_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Pekerjaan</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">{{ $patient->occupation ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">No. HP</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">{{ $patient->phone ?: '-' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-slate-400">Alamat</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">{{ $patient->address ?: '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Data Medis --}}
        <div data-tab-panel="medis" class="hidden">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                @php $history = $patient->medicalHistory; @endphp
                <form method="POST" action="{{ route('patients.medical-history.update', $patient) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div>
                            <label for="blood_type" class="mb-1.5 block text-sm font-medium text-slate-700">Golongan Darah</label>
                            <select id="blood_type" name="blood_type"
                                class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                <option value="">-</option>
                                @foreach (['A', 'B', 'AB', 'O'] as $type)
                                    <option value="{{ $type }}" {{ old('blood_type', $history?->blood_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="systolic" class="mb-1.5 block text-sm font-medium text-slate-700">Tekanan Darah Sistolik</label>
                            <div class="relative">
                                <input type="number" id="systolic" name="systolic" value="{{ old('systolic', $history?->systolic) }}" placeholder="120"
                                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400">mmHg</span>
                            </div>
                        </div>
                        <div>
                            <label for="diastolic" class="mb-1.5 block text-sm font-medium text-slate-700">Tekanan Darah Diastolik</label>
                            <div class="relative">
                                <input type="number" id="diastolic" name="diastolic" value="{{ old('diastolic', $history?->diastolic) }}" placeholder="80"
                                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400">mmHg</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="mb-2 block text-sm font-medium text-slate-700">Riwayat Penyakit</label>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @foreach (['heart_disease' => 'Penyakit Jantung', 'diabetes' => 'Diabetes', 'haemophilia' => 'Haemophilia', 'hepatitis' => 'Hepatitis'] as $field => $label)
                                <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                                    <input type="checkbox" name="{{ $field }}" value="1" {{ old($field, $history?->$field) ? 'checked' : '' }}
                                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-5">
                        <div>
                            <label for="other_disease" class="mb-1.5 block text-sm font-medium text-slate-700">Penyakit Lainnya</label>
                            <input type="text" id="other_disease" name="other_disease" value="{{ old('other_disease', $history?->other_disease) }}"
                                class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        </div>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="drug_allergy" class="mb-1.5 block text-sm font-medium text-slate-700">Alergi Obat</label>
                                <input type="text" id="drug_allergy" name="drug_allergy" value="{{ old('drug_allergy', $history?->drug_allergy) }}"
                                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label for="food_allergy" class="mb-1.5 block text-sm font-medium text-slate-700">Alergi Makanan</label>
                                <input type="text" id="food_allergy" name="food_allergy" value="{{ old('food_allergy', $history?->food_allergy) }}"
                                    class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="notes" class="mb-1.5 block text-sm font-medium text-slate-700">Catatan</label>
                        <textarea id="notes" name="notes" rows="3"
                            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">{{ old('notes', $history?->notes) }}</textarea>
                    </div>

                    <div class="mt-6 flex justify-end border-t border-slate-100 pt-5">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                            <i class="fa-solid fa-check"></i> Simpan Data Medis
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Odontogram --}}
        <div data-tab-panel="odontogram" class="hidden">
            @if ($patient->odontograms->isEmpty())
                <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-sm">
                    <i class="fa-solid fa-tooth mb-3 block text-3xl text-slate-300"></i>
                    <p class="text-sm font-medium text-slate-500">Odontogram akan tersedia setelah pasien memiliki kunjungan pemeriksaan.</p>
                </div>
            @else
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="text-sm font-semibold text-slate-900">Odontogram</h3>
                        <form method="GET" class="flex items-center gap-2">
                            <input type="hidden" name="tab" value="odontogram">
                            <label for="odontogram_id" class="text-xs text-slate-500">Kunjungan:</label>
                            <select name="odontogram_id" id="odontogram_id" onchange="this.form.submit()"
                                class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                @foreach ($patient->odontograms as $item)
                                    <option value="{{ $item->id }}" {{ $selectedOdontogram?->id === $item->id ? 'selected' : '' }}>
                                        {{ $item->examination_date->translatedFormat('d F Y') }} &mdash; {{ $item->doctor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    @php
                        $teethByNumber = $selectedOdontogram->teeth->keyBy('tooth_number');
                        $notableTeeth = $teethByNumber->filter(fn ($t) => $t->condition && $t->condition !== 'Normal')->sortBy('tooth_number');
                    @endphp

                    @include('partials.odontogram-grid', ['teethByNumber' => $teethByNumber, 'interactive' => false])

                    @if ($notableTeeth->isNotEmpty())
                        <div class="mt-6 divide-y divide-slate-100 border-t border-slate-100 pt-4">
                            @foreach ($notableTeeth as $tooth)
                                <div class="py-2 text-sm">
                                    <span class="font-semibold text-slate-900">Gigi {{ $tooth->tooth_number }}</span>
                                    <span class="text-slate-500">&mdash; {{ $tooth->condition }}{{ $tooth->surfaces ? " ({$tooth->surfaces})" : '' }}</span>
                                    @if ($tooth->notes)
                                        <p class="text-xs text-slate-400">{{ $tooth->notes }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-6 border-t border-slate-100 pt-4 text-sm text-slate-400">Semua gigi dalam kondisi normal.</p>
                    @endif

                    <div class="mt-4 border-t border-slate-100 pt-4 text-right">
                        <a href="{{ route('visits.show', $selectedOdontogram->visit_id) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:text-blue-700">
                            Lihat Detail Pemeriksaan <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Riwayat Perawatan --}}
        <div data-tab-panel="riwayat" class="hidden space-y-4">
            @forelse ($patient->visits as $visit)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $visit->visit_date->translatedFormat('d F Y') }}</p>
                            <p class="text-xs text-slate-400">Dokter: {{ $visit->doctor->name }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $visit->status_badge_class }}">
                            {{ $visit->status_label }}
                        </span>
                    </div>

                    <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-3 border-t border-slate-100 pt-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs text-slate-400">Keluhan</dt>
                            <dd class="mt-0.5 text-sm text-slate-700">{{ $visit->complaint ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-400">Diagnosis</dt>
                            <dd class="mt-0.5 text-sm text-slate-700">
                                {{ $visit->diagnosis ?: '-' }}
                                @if ($visit->icd10_code)<span class="text-slate-400">({{ $visit->icd10_code }})</span>@endif
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-slate-400">Tindakan</dt>
                            <dd class="mt-0.5 text-sm text-slate-700">
                                @forelse ($visit->treatments as $treatment)
                                    {{ $treatment->treatment }}{{ $treatment->tooth_number ? " (Gigi {$treatment->tooth_number})" : '' }}{{ ! $loop->last ? ', ' : '' }}
                                @empty
                                    -
                                @endforelse
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <a href="{{ route('visits.show', $visit) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:text-blue-700">
                            Lihat Detail Pemeriksaan <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-sm">
                    <i class="fa-solid fa-clock-rotate-left mb-3 block text-3xl text-slate-300"></i>
                    <p class="text-sm font-medium text-slate-500">Belum ada riwayat perawatan untuk pasien ini.</p>
                </div>
            @endforelse
        </div>

        {{-- Lampiran --}}
        <div data-tab-panel="lampiran" class="hidden">
            @if ($patient->attachments->isEmpty())
                <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-sm">
                    <i class="fa-solid fa-paperclip mb-3 block text-3xl text-slate-300"></i>
                    <p class="text-sm font-medium text-slate-500">Belum ada lampiran untuk pasien ini.</p>
                </div>
            @else
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <h3 class="mb-5 text-sm font-semibold text-slate-900">Lampiran</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($patient->attachments as $attachment)
                            <div class="overflow-hidden rounded-xl border border-slate-200">
                                <div class="flex h-32 items-center justify-center bg-slate-50">
                                    @if ($attachment->is_image)
                                        <img src="{{ $attachment->url }}" alt="{{ $attachment->original_name }}" class="h-full w-full object-cover">
                                    @else
                                        <i class="fa-solid fa-file-pdf text-4xl text-red-400"></i>
                                    @endif
                                </div>
                                <div class="p-3">
                                    <p class="truncate text-xs font-semibold text-slate-900" title="{{ $attachment->original_name }}">{{ $attachment->original_name }}</p>
                                    <p class="mt-0.5 text-xs text-slate-400">{{ $attachment->type_label }} &middot; {{ $attachment->created_at->translatedFormat('d M Y') }}</p>
                                    @if ($attachment->description)
                                        <p class="mt-1 text-xs text-slate-500">{{ $attachment->description }}</p>
                                    @endif
                                    <a href="{{ $attachment->url }}" target="_blank" class="mt-2 inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-700">
                                        <i class="fa-solid fa-eye"></i> Lihat
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
