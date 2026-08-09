@php $patient = $patient ?? null; @endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="name" class="mb-1.5 block text-sm font-medium text-slate-700">Nama Lengkap</label>
        <input type="text" id="name" name="name" value="{{ old('name', $patient->name ?? '') }}" required autofocus
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 @error('name') border-red-300 @enderror"
            placeholder="cth. Haikal Ramadhan">
        @error('name') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="nik" class="mb-1.5 block text-sm font-medium text-slate-700">NIK</label>
        <input type="text" id="nik" name="nik" value="{{ old('nik', $patient->nik ?? '') }}" inputmode="numeric" maxlength="16"
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 @error('nik') border-red-300 @enderror"
            placeholder="16 digit NIK (opsional)">
        @error('nik') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="phone" class="mb-1.5 block text-sm font-medium text-slate-700">No. HP</label>
        <input type="text" id="phone" name="phone" value="{{ old('phone', $patient->phone ?? '') }}"
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 @error('phone') border-red-300 @enderror"
            placeholder="cth. 081234567890">
        @error('phone') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="birth_place" class="mb-1.5 block text-sm font-medium text-slate-700">Tempat Lahir</label>
        <input type="text" id="birth_place" name="birth_place" value="{{ old('birth_place', $patient->birth_place ?? '') }}"
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 @error('birth_place') border-red-300 @enderror"
            placeholder="cth. Jakarta">
        @error('birth_place') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="birth_date" class="mb-1.5 block text-sm font-medium text-slate-700">Tanggal Lahir</label>
        <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date', optional($patient->birth_date ?? null)->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}"
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 @error('birth_date') border-red-300 @enderror">
        @error('birth_date') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="gender" class="mb-1.5 block text-sm font-medium text-slate-700">Jenis Kelamin</label>
        <select id="gender" name="gender"
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 @error('gender') border-red-300 @enderror">
            <option value="">Pilih jenis kelamin</option>
            <option value="L" {{ old('gender', $patient->gender ?? '') === 'L' ? 'selected' : '' }}>Laki-laki</option>
            <option value="P" {{ old('gender', $patient->gender ?? '') === 'P' ? 'selected' : '' }}>Perempuan</option>
        </select>
        @error('gender') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="occupation" class="mb-1.5 block text-sm font-medium text-slate-700">Pekerjaan</label>
        <input type="text" id="occupation" name="occupation" value="{{ old('occupation', $patient->occupation ?? '') }}"
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 @error('occupation') border-red-300 @enderror"
            placeholder="cth. Karyawan Swasta">
        @error('occupation') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="address" class="mb-1.5 block text-sm font-medium text-slate-700">Alamat</label>
        <textarea id="address" name="address" rows="3"
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 @error('address') border-red-300 @enderror"
            placeholder="Alamat lengkap pasien">{{ old('address', $patient->address ?? '') }}</textarea>
        @error('address') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
