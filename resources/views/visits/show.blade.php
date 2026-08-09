@extends('layouts.app')

@section('title', 'Pemeriksaan - ' . $visit->patient->name)
@section('page-title', 'Pemeriksaan Pasien')
@section('page-description', $visit->visit_date->translatedFormat('d F Y'))

@section('content')
    <div class="mb-4">
        <a href="{{ url()->previous() === url()->current() ? route('queues.index') : url()->previous() }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-700">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- Patient + visit header --}}
    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-4">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-xl font-semibold text-blue-600">
                    {{ strtoupper(substr($visit->patient->name, 0, 1)) }}
                </span>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 font-mono text-xs font-medium text-slate-600">
                            {{ $visit->patient->medical_record_number }}
                        </span>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $visit->status_badge_class }}">
                            {{ $visit->status_label }}
                        </span>
                    </div>
                    <h2 class="mt-1.5 text-lg font-semibold text-slate-900">
                        <a href="{{ route('patients.show', $visit->patient) }}" class="hover:text-blue-600">{{ $visit->patient->name }}</a>
                    </h2>
                    <p class="text-sm text-slate-500">
                        {{ $visit->patient->gender_label }}
                        @if ($visit->patient->birth_date?->age !== null) &middot; {{ $visit->patient->birth_date->age }} tahun @endif
                        &middot; {{ $visit->patient->phone ?: 'No. HP belum diisi' }}
                    </p>
                </div>
            </div>

            @if ($canEdit)
                <form action="{{ route('visits.complete', $visit) }}" method="POST" data-confirm
                    data-confirm-title="Selesaikan Pemeriksaan?"
                    data-confirm-text="Pastikan seluruh data pemeriksaan sudah benar. Data tidak dapat diubah lagi setelah diselesaikan."
                    data-confirm-button="Ya, Selesaikan"
                    data-confirm-icon="question">
                    @csrf
                    <button type="submit"
                        class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                        <i class="fa-solid fa-circle-check"></i> Selesaikan Pemeriksaan
                    </button>
                </form>
            @endif
        </div>

        <div class="mt-5 grid grid-cols-2 gap-4 border-t border-slate-100 pt-5 sm:grid-cols-4">
            <div>
                <p class="text-xs text-slate-400">Dokter</p>
                <p class="text-sm font-medium text-slate-800">{{ $visit->doctor->name }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Tanggal Kunjungan</p>
                <p class="text-sm font-medium text-slate-800">{{ $visit->visit_date->translatedFormat('d F Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">No. Antrean</p>
                <p class="text-sm font-medium text-slate-800">{{ $visit->queue->queue_number ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Golongan Darah</p>
                <p class="text-sm font-medium text-slate-800">{{ $visit->patient->medicalHistory?->blood_type ?: '-' }}</p>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Keluhan / Diagnosis / ICD-10 / Catatan --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h3 class="mb-5 text-sm font-semibold text-slate-900">Pemeriksaan</h3>

            @if ($canEdit)
                <form method="POST" action="{{ route('visits.update', $visit) }}">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="complaint" class="mb-1.5 block text-sm font-medium text-slate-700">Keluhan</label>
                            <textarea id="complaint" name="complaint" rows="2"
                                class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                placeholder="cth. Gigi sakit ketika mengunyah">{{ old('complaint', $visit->complaint) }}</textarea>
                        </div>
                        <div>
                            <label for="diagnosis" class="mb-1.5 block text-sm font-medium text-slate-700">Diagnosis</label>
                            <input type="text" id="diagnosis" name="diagnosis" value="{{ old('diagnosis', $visit->diagnosis) }}"
                                class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                placeholder="cth. Dental Caries">
                        </div>
                        <div>
                            <label for="icd10_code" class="mb-1.5 block text-sm font-medium text-slate-700">ICD-10</label>
                            <input type="text" id="icd10_code" name="icd10_code" value="{{ old('icd10_code', $visit->icd10_code) }}"
                                class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                placeholder="cth. K02.9">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="notes" class="mb-1.5 block text-sm font-medium text-slate-700">Catatan</label>
                            <textarea id="notes" name="notes" rows="3"
                                class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                placeholder="Catatan tambahan pemeriksaan">{{ old('notes', $visit->notes) }}</textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end border-t border-slate-100 pt-5">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan
                        </button>
                    </div>
                </form>
            @else
                <dl class="grid grid-cols-1 gap-x-6 gap-y-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-slate-400">Keluhan</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">{{ $visit->complaint ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">Diagnosis</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">{{ $visit->diagnosis ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-400">ICD-10</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">{{ $visit->icd10_code ?: '-' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-slate-400">Catatan</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">{{ $visit->notes ?: '-' }}</dd>
                    </div>
                </dl>
            @endif
        </div>

        {{-- Odontogram --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">Odontogram</h3>
                <div class="flex items-center gap-1.5 text-xs text-slate-400">
                    <span class="h-2 w-2 rounded-full bg-blue-500"></span> Ada kondisi
                </div>
            </div>

            @php
                $odontogram = $visit->odontogram;
                $teethByNumber = $odontogram?->teeth->keyBy('tooth_number') ?? collect();
                $notableTeeth = $teethByNumber->filter(fn ($t) => $t->condition && $t->condition !== 'Normal')->sortBy('tooth_number');
            @endphp

            @include('partials.odontogram-grid', [
                'teethByNumber' => $teethByNumber,
                'interactive' => $canEdit,
                'saveUrl' => $canEdit ? route('visits.odontogram.teeth.update', $visit) : null,
            ])

            <div class="mt-6 border-t border-slate-100 pt-4">
                <div data-notable-list class="divide-y divide-slate-100 {{ $notableTeeth->isEmpty() ? 'hidden' : '' }}">
                    @foreach ($notableTeeth as $tooth)
                        <div class="py-2 text-sm" data-notable-item="{{ $tooth->tooth_number }}">
                            <span class="font-semibold text-slate-900">Gigi {{ $tooth->tooth_number }}</span>
                            <span class="text-slate-500" data-notable-text>&mdash; {{ $tooth->condition }}{{ $tooth->surfaces ? " ({$tooth->surfaces})" : '' }}</span>
                            <p class="text-xs text-slate-400 {{ $tooth->notes ? '' : 'hidden' }}" data-notable-notes>{{ $tooth->notes }}</p>
                        </div>
                    @endforeach
                </div>
                <p data-notable-empty class="text-sm text-slate-400 {{ $notableTeeth->isNotEmpty() ? 'hidden' : '' }}">Belum ada gigi dengan kondisi khusus.</p>
            </div>

            <div class="mt-6 border-t border-slate-100 pt-5">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Temuan Umum</p>

                @if ($canEdit)
                    <form method="POST" action="{{ route('visits.odontogram.update', $visit) }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Oklusi</label>
                                <input type="text" name="occlusion" value="{{ old('occlusion', $odontogram?->occlusion) }}"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Torus Palatinus</label>
                                <input type="text" name="torus_palatinus" value="{{ old('torus_palatinus', $odontogram?->torus_palatinus) }}"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Torus Mandibularis</label>
                                <input type="text" name="torus_mandibularis" value="{{ old('torus_mandibularis', $odontogram?->torus_mandibularis) }}"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Palatum</label>
                                <input type="text" name="palate" value="{{ old('palate', $odontogram?->palate) }}"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Diastema</label>
                                <input type="text" name="diastema" value="{{ old('diastema', $odontogram?->diastema) }}"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Anomali Gigi</label>
                                <input type="text" name="dental_anomaly" value="{{ old('dental_anomaly', $odontogram?->dental_anomaly) }}"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Catatan Umum</label>
                            <textarea name="notes" rows="2"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">{{ old('notes', $odontogram?->notes) }}</textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                <i class="fa-solid fa-floppy-disk"></i> Simpan Temuan Umum
                            </button>
                        </div>
                    </form>
                @else
                    @php
                        $generalFindings = collect([
                            'Oklusi' => $odontogram?->occlusion, 'Torus Palatinus' => $odontogram?->torus_palatinus,
                            'Torus Mandibularis' => $odontogram?->torus_mandibularis, 'Palatum' => $odontogram?->palate,
                            'Diastema' => $odontogram?->diastema, 'Anomali Gigi' => $odontogram?->dental_anomaly,
                        ])->filter();
                    @endphp
                    @if ($generalFindings->isNotEmpty() || $odontogram?->notes)
                        <dl class="grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-3">
                            @foreach ($generalFindings as $label => $value)
                                <div>
                                    <dt class="text-xs text-slate-400">{{ $label }}</dt>
                                    <dd class="mt-0.5 text-slate-700">{{ $value }}</dd>
                                </div>
                            @endforeach
                            @if ($odontogram?->notes)
                                <div class="sm:col-span-3">
                                    <dt class="text-xs text-slate-400">Catatan Umum</dt>
                                    <dd class="mt-0.5 text-slate-700">{{ $odontogram->notes }}</dd>
                                </div>
                            @endif
                        </dl>
                    @else
                        <p class="text-sm text-slate-400">Tidak ada temuan umum dicatat.</p>
                    @endif
                @endif
            </div>
        </div>

        {{-- Tindakan / Perawatan --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h3 class="mb-5 text-sm font-semibold text-slate-900">Tindakan / Perawatan</h3>

            @if ($canEdit)
                <form method="POST" action="{{ route('visits.treatments.store', $visit) }}"
                    class="mb-6 grid grid-cols-1 gap-4 rounded-xl border border-slate-200 bg-slate-50/60 p-4 sm:grid-cols-2 lg:grid-cols-5">
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Gigi</label>
                        <input type="text" name="tooth_number" value="{{ old('tooth_number') }}" placeholder="cth. 16"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Diagnosis</label>
                        <input type="text" name="diagnosis" value="{{ old('diagnosis') }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">ICD-10</label>
                        <input type="text" name="icd10_code" value="{{ old('icd10_code') }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Tindakan <span class="text-red-500">*</span></label>
                        <input type="text" name="treatment" value="{{ old('treatment') }}" required placeholder="cth. Penambalan"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Keterangan</label>
                        <input type="text" name="notes" value="{{ old('notes') }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div class="sm:col-span-2 lg:col-span-5">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                            <i class="fa-solid fa-plus"></i> Tambah Tindakan
                        </button>
                    </div>
                </form>

                @error('treatment')
                    <p class="mb-4 text-xs text-red-600">{{ $message }}</p>
                @enderror
            @endif

            @forelse ($visit->treatments as $treatment)
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 py-3 last:border-0">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($treatment->tooth_number)
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">Gigi {{ $treatment->tooth_number }}</span>
                            @endif
                            <p class="font-medium text-slate-900">{{ $treatment->treatment }}</p>
                        </div>
                        @if ($treatment->diagnosis)
                            <p class="mt-0.5 text-xs text-slate-500">{{ $treatment->diagnosis }} @if($treatment->icd10_code)({{ $treatment->icd10_code }})@endif</p>
                        @endif
                        @if ($treatment->notes)
                            <p class="mt-0.5 text-xs text-slate-400">{{ $treatment->notes }}</p>
                        @endif
                    </div>
                    @if ($canEdit)
                        <form action="{{ route('visits.treatments.destroy', [$visit, $treatment]) }}" method="POST"
                            data-confirm data-confirm-text="Tindakan &quot;{{ $treatment->treatment }}&quot; akan dihapus.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="shrink-0 text-slate-400 hover:text-red-600" title="Hapus">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-400">Belum ada tindakan ditambahkan.</p>
            @endforelse
        </div>

        {{-- Lampiran --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <h3 class="mb-5 text-sm font-semibold text-slate-900">Lampiran</h3>

            @if ($canEdit)
                <form method="POST" action="{{ route('visits.attachments.store', $visit) }}" enctype="multipart/form-data"
                    class="mb-6 grid grid-cols-1 gap-4 rounded-xl border border-slate-200 bg-slate-50/60 p-4 sm:grid-cols-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Jenis</label>
                        <select name="type" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            <option value="">Pilih jenis</option>
                            @foreach (\App\Models\Attachment::TYPES as $value => $label)
                                <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-slate-600">File <span class="font-normal text-slate-400">(jpg, jpeg, png, pdf &mdash; maks. 2MB)</span></label>
                        <input type="file" name="file" required accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-blue-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Keterangan</label>
                        <input type="text" name="description" value="{{ old('description') }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div class="sm:col-span-4">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                            <i class="fa-solid fa-upload"></i> Unggah Lampiran
                        </button>
                    </div>
                </form>

                @error('file')
                    <p class="mb-4 text-xs text-red-600">{{ $message }}</p>
                @enderror
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($visit->attachments as $attachment)
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
                            <div class="mt-2 flex items-center justify-between">
                                <a href="{{ $attachment->url }}" target="_blank" class="text-xs font-medium text-blue-600 hover:text-blue-700">
                                    <i class="fa-solid fa-eye"></i> Lihat
                                </a>
                                @if ($canEdit)
                                    <form action="{{ route('visits.attachments.destroy', [$visit, $attachment]) }}" method="POST"
                                        data-confirm data-confirm-text="Lampiran &quot;{{ $attachment->original_name }}&quot; akan dihapus.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-red-500 hover:text-red-700">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-6 text-center text-sm text-slate-400">Belum ada lampiran.</div>
                @endforelse
            </div>
        </div>
    </div>

    @if ($canEdit)
        @include('partials.odontogram-tooth-modal')
    @endif
@endsection
