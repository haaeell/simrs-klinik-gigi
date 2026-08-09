@php
    $room = $room ?? null;
@endphp

<div class="grid grid-cols-1 gap-5">
    <div>
        <label for="name" class="mb-1.5 block text-sm font-medium text-slate-700">Nama Ruangan <span class="text-red-500">*</span></label>
        <input type="text" id="name" name="name" required value="{{ old('name', $room?->name) }}" placeholder="cth. Ruang 1"
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        @error('name')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="doctor_id" class="mb-1.5 block text-sm font-medium text-slate-700">Dokter Bertugas</label>
        <select id="doctor_id" name="doctor_id"
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            <option value="">(Belum ditentukan)</option>
            @foreach ($doctors as $doctor)
                <option value="{{ $doctor->id }}" {{ (string) old('doctor_id', $room?->doctor_id) === (string) $doctor->id ? 'selected' : '' }}>{{ $doctor->name }}</option>
            @endforeach
        </select>
        <p class="mt-1.5 text-xs text-slate-400">Hanya menampilkan dokter yang belum ditugaskan ke ruangan lain. Satu dokter hanya bisa bertugas di satu ruangan.</p>
        @error('doctor_id')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $room?->is_active ?? true) ? 'checked' : '' }}
            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-200">
        Aktif (tampil sebagai pilihan saat memanggil antrean)
    </label>
</div>
