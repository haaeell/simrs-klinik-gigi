@php $user = $user ?? null; @endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="name" class="mb-1.5 block text-sm font-medium text-slate-700">Nama Lengkap</label>
        <input type="text" id="name" name="name" value="{{ old('name', $user->name ?? '') }}" required autofocus
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 @error('name') border-red-300 @enderror"
            placeholder="cth. drg. Ahmad Fauzi">
        @error('name') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 @error('email') border-red-300 @enderror"
            placeholder="nama@klinik.com">
        @error('email') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="role" class="mb-1.5 block text-sm font-medium text-slate-700">Role</label>
        <select id="role" name="role" required
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 @error('role') border-red-300 @enderror">
            <option value="" disabled {{ old('role', $user->role ?? '') === '' ? 'selected' : '' }}>Pilih role</option>
            <option value="admin" {{ old('role', $user->role ?? '') === 'admin' ? 'selected' : '' }}>Admin / Petugas</option>
            <option value="dokter" {{ old('role', $user->role ?? '') === 'dokter' ? 'selected' : '' }}>Dokter</option>
        </select>
        @error('role') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">
            Kata Sandi
            @if ($user) <span class="font-normal text-slate-400">(kosongkan jika tidak ingin mengubah)</span> @endif
        </label>
        <input type="password" id="password" name="password"
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 @error('password') border-red-300 @enderror"
            placeholder="{{ $user ? '••••••••' : 'Minimal 6 karakter' }}">
        @error('password') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
