<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Klinik Gigi</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased print:bg-white">

    <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-900/50 lg:hidden print:hidden"></div>

    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 ease-in-out lg:translate-x-0 print:hidden">
        <div class="flex h-16 shrink-0 items-center gap-2 border-b border-slate-200 px-5">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-white">
                <i class="fa-solid fa-tooth"></i>
            </span>
            <div class="leading-tight">
                <p class="text-sm font-semibold text-slate-900">Klinik Gigi</p>
                <p class="text-xs text-slate-400">Sistem Antrean &amp; RM</p>
            </div>
            <button id="sidebar-close" type="button" class="ml-auto text-slate-400 hover:text-slate-600 lg:hidden">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            @php
                $user = auth()->user();

                if ($user->isPasien()) {
                    $menuItems = [
                        ['label' => 'Dashboard', 'icon' => 'fa-house', 'route' => 'patient.dashboard'],
                        ['label' => 'Antrean Saya', 'icon' => 'fa-list-ol', 'route' => 'patient.queue'],
                        ['label' => 'Riwayat Kunjungan', 'icon' => 'fa-clock-rotate-left', 'route' => 'patient.history'],
                        ['label' => 'Odontogram', 'icon' => 'fa-tooth', 'route' => 'patient.odontogram'],
                        ['label' => 'Jadwal Kontrol', 'icon' => 'fa-calendar-check', 'route' => 'patient.control-schedules'],
                        ['label' => 'Notifikasi', 'icon' => 'fa-bell', 'route' => 'patient.notifications'],
                        ['label' => 'Profil', 'icon' => 'fa-user', 'route' => 'patient.profile'],
                    ];
                } else {
                    $menuItems = [
                        ['label' => 'Dashboard', 'icon' => 'fa-house', 'route' => 'dashboard'],
                        ['label' => $user->isAdmin() ? 'Antrean' : 'Antrean Pasien', 'icon' => 'fa-list-ol', 'route' => 'queues.index'],
                        ['label' => 'Pasien', 'icon' => 'fa-users', 'route' => 'patients.index'],
                        ['label' => 'Rekam Medis', 'icon' => 'fa-notes-medical', 'route' => 'visits.index'],
                        ['label' => 'Laporan', 'icon' => 'fa-chart-line', 'route' => 'reports.visits'],
                        ['label' => 'Pengguna', 'icon' => 'fa-user-doctor', 'route' => 'users.index', 'adminOnly' => true],
                    ];
                }
            @endphp

            @foreach ($menuItems as $item)
                @continue(($item['adminOnly'] ?? false) && ! $user->isAdmin())
                @php
                    // Patient-portal routes all share a "patient." prefix (one page per menu item), so the
                    // section key must be the full route name there; admin/dokter routes group multiple pages
                    // under one resource segment (e.g. "visits.show" should still highlight "Rekam Medis").
                    $prefix = str_starts_with($item['route'], 'patient.') ? $item['route'] : explode('.', $item['route'])[0];
                    $isActive = request()->routeIs($prefix.'*') || request()->routeIs($prefix.'.*');
                    $exists = \Illuminate\Support\Facades\Route::has($item['route']);
                @endphp
                <a href="{{ $exists ? route($item['route']) : '#' }}"
                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors
                        {{ $isActive ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}
                        {{ $exists ? '' : 'pointer-events-none opacity-40' }}">
                    <i class="fa-solid {{ $item['icon'] }} w-4 text-center {{ $isActive ? 'text-blue-600' : 'text-slate-400' }}"></i>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="shrink-0 border-t border-slate-200 p-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-red-50 hover:text-red-600">
                    <i class="fa-solid fa-right-from-bracket w-4 text-center text-slate-400"></i>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <div class="flex min-h-screen flex-col lg:pl-64 print:pl-0">
        <header class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-4 border-b border-slate-200 bg-white/80 px-4 backdrop-blur sm:px-6 print:hidden">
            <button id="sidebar-open" type="button" class="text-slate-500 hover:text-slate-700 lg:hidden">
                <i class="fa-solid fa-bars text-lg"></i>
            </button>

            <div class="min-w-0 flex-1">
                <h1 class="truncate text-base font-semibold text-slate-900 sm:text-lg">@yield('page-title', 'Dashboard')</h1>
                @hasSection('page-description')
                    <p class="hidden truncate text-xs text-slate-400 sm:block">@yield('page-description')</p>
                @endif
            </div>

            @if ($user->isPasien())
                @php
                    $reminders = \App\Models\ControlSchedule::remindersFor($user->patient_id)->pluck('text');
                    $queueReminder = \App\Models\Queue::reminderTextFor($user->patient_id);
                    if ($queueReminder) {
                        $reminders->push($queueReminder);
                    }
                @endphp
                <div class="relative shrink-0">
                    <button id="notif-menu-button" type="button" class="relative flex h-9 w-9 items-center justify-center rounded-xl text-slate-500 hover:bg-slate-50 hover:text-slate-700">
                        <i class="fa-solid fa-bell"></i>
                        @if ($reminders->isNotEmpty())
                            <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-red-500"></span>
                        @endif
                    </button>
                    <div id="notif-menu" class="absolute right-0 z-30 mt-2 hidden w-72 rounded-xl border border-slate-200 bg-white p-2 shadow-lg">
                        @forelse ($reminders as $reminder)
                            <div class="rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                <i class="fa-solid fa-circle-exclamation mr-1.5 text-amber-500"></i> {{ $reminder }}
                            </div>
                        @empty
                            <p class="px-3 py-2 text-sm text-slate-400">Tidak ada notifikasi.</p>
                        @endforelse
                        @if (\Illuminate\Support\Facades\Route::has('patient.notifications'))
                            <a href="{{ route('patient.notifications') }}" class="mt-1 block rounded-lg px-3 py-2 text-center text-xs font-medium text-blue-600 hover:bg-blue-50">
                                Lihat Semua Notifikasi
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            <div class="relative shrink-0">
                <button id="user-menu-button" type="button" class="flex items-center gap-3 rounded-xl px-2 py-1.5 hover:bg-slate-50">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                    <span class="hidden text-left sm:block">
                        <span class="block text-sm font-medium leading-tight text-slate-900">{{ $user->name }}</span>
                        <span class="block text-xs leading-tight text-slate-400">{{ $user->role_label }}</span>
                    </span>
                    <i class="fa-solid fa-chevron-down hidden text-xs text-slate-400 sm:block"></i>
                </button>

                <div id="user-menu" class="absolute right-0 z-30 mt-2 hidden w-48 rounded-xl border border-slate-200 bg-white p-1.5 shadow-lg">
                    <div class="border-b border-slate-100 px-3 py-2 sm:hidden">
                        <p class="text-sm font-medium text-slate-900">{{ $user->name }}</p>
                        <p class="text-xs text-slate-400">{{ $user->role_label }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-slate-600 hover:bg-red-50 hover:text-red-600">
                            <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 py-6 sm:px-6 lg:py-8 print:p-0">
            @yield('content')
        </main>
    </div>

    @if (session('success'))
        <script>
            window.addEventListener('DOMContentLoaded', () => Swal.fire({
                icon: 'success', title: @json(session('success')),
                toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true,
            }));
        </script>
    @endif

    @if (session('error'))
        <script>window.addEventListener('DOMContentLoaded', () => Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')) }));</script>
    @endif

    @stack('scripts')
</body>
</html>
