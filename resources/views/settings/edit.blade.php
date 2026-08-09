@extends('layouts.app')

@section('title', 'Pengaturan Sistem')
@section('page-title', 'Pengaturan Sistem')
@section('page-description', 'Identitas klinik yang tampil di seluruh aplikasi')

@section('content')
    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-slate-900">Logo Klinik</h3>
            <div class="flex items-center gap-4">
                <span id="logo-preview-wrap" class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-blue-50 text-2xl text-blue-600">
                    @if ($setting->logo_url)
                        <img id="logo-preview" src="{{ $setting->logo_url }}" alt="Logo" class="h-full w-full object-cover">
                    @else
                        <i id="logo-placeholder" class="fa-solid fa-tooth"></i>
                        <img id="logo-preview" src="" alt="Logo" class="hidden h-full w-full object-cover">
                    @endif
                </span>
                <div>
                    <input type="file" name="logo" id="logo" accept="image/*"
                        class="block text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-blue-700">
                    <p class="mt-1 text-xs text-slate-400">JPG/PNG, maks. 1MB. Kosongkan jika tidak ingin mengganti.</p>
                    @if ($setting->logo_url)
                        <label class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-200">
                            Hapus logo saat ini
                        </label>
                    @endif
                </div>
            </div>
            @error('logo')
                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-slate-900">Identitas Klinik</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="clinic_name" class="mb-1.5 block text-sm font-medium text-slate-700">Nama Klinik <span class="text-red-500">*</span></label>
                    <input type="text" id="clinic_name" name="clinic_name" required value="{{ old('clinic_name', $setting->clinic_name) }}"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    @error('clinic_name')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="address" class="mb-1.5 block text-sm font-medium text-slate-700">Alamat</label>
                    <textarea id="address" name="address" rows="2"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">{{ old('address', $setting->address) }}</textarea>
                </div>
                <div>
                    <label for="phone" class="mb-1.5 block text-sm font-medium text-slate-700">No. Telepon</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $setting->phone) }}"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $setting->email) }}"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="mb-1 text-sm font-semibold text-slate-900">Warna Utama</h3>
            <p class="mb-4 text-xs text-slate-400">
                Dipakai pada logo, halaman login, layar antrean, dan kop cetak. Warna tombol/aksen lain di aplikasi tetap memakai tema bawaan.
            </p>
            <div class="flex items-center gap-3">
                <input type="color" name="primary_color" id="primary_color" value="{{ old('primary_color', $setting->primary_color) }}"
                    class="h-11 w-16 cursor-pointer rounded-lg border border-slate-300">
                <input type="text" value="{{ old('primary_color', $setting->primary_color) }}" id="primary_color_text"
                    class="w-32 rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm font-mono focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
            </div>
            @error('primary_color')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Pengaturan
            </button>
        </div>
    </form>

    <script>
        const colorPicker = document.getElementById('primary_color');
        const colorText = document.getElementById('primary_color_text');
        colorPicker.addEventListener('input', () => colorText.value = colorPicker.value);
        colorText.addEventListener('input', () => {
            if (/^#[0-9A-Fa-f]{6}$/.test(colorText.value)) {
                colorPicker.value = colorText.value;
            }
        });

        document.getElementById('logo').addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            const preview = document.getElementById('logo-preview');
            const placeholder = document.getElementById('logo-placeholder');
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('hidden');
            placeholder?.classList.add('hidden');
        });
    </script>
@endsection
