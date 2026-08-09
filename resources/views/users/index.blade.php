@extends('layouts.app')

@section('title', 'Pengguna')
@section('page-title', 'Pengguna')
@section('page-description', 'Kelola akun admin dan dokter')

@section('content')
    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" class="relative w-full sm:max-w-xs">
            <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..."
                class="w-full rounded-xl border border-slate-300 py-2.5 pl-9 pr-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
        </form>

        <a href="{{ route('users.create') }}"
            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-blue-700">
            <i class="fa-solid fa-plus"></i>
            Tambah Pengguna
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Nama</th>
                        <th class="px-5 py-3 font-medium">Email</th>
                        <th class="px-5 py-3 font-medium">Role</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-5 py-3.5 font-medium text-slate-900">{{ $user->name }}</td>
                            <td class="px-5 py-3.5 text-slate-500">{{ $user->email }}</td>
                            <td class="px-5 py-3.5">
                                @if ($user->isAdmin())
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-violet-50 px-2.5 py-1 text-xs font-medium text-violet-700">
                                        <i class="fa-solid fa-user-shield"></i> Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                                        <i class="fa-solid fa-user-doctor"></i> Dokter
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('users.edit', $user) }}"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 hover:text-blue-600" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    @if ($user->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" data-confirm
                                            data-confirm-title="Hapus Pengguna?"
                                            data-confirm-text="Akun {{ $user->name }} akan dihapus secara permanen.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-500 hover:bg-red-50 hover:text-red-600" title="Hapus">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-16 text-center">
                                <i class="fa-solid fa-user-doctor mb-3 block text-3xl text-slate-300"></i>
                                <p class="text-sm font-medium text-slate-500">Belum ada pengguna yang cocok.</p>
                                <a href="{{ route('users.create') }}" class="mt-3 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                    <i class="fa-solid fa-plus"></i> Tambah Pengguna
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($users->hasPages())
        <div class="mt-4">{{ $users->links() }}</div>
    @endif
@endsection
