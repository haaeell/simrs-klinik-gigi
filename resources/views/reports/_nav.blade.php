@php
    $reportLinks = [
        ['route' => 'reports.visits', 'label' => 'Kunjungan', 'icon' => 'fa-calendar-check'],
        ['route' => 'reports.queues', 'label' => 'Antrean', 'icon' => 'fa-list-ol'],
        ['route' => 'reports.patients', 'label' => 'Pasien', 'icon' => 'fa-users'],
        ['route' => 'reports.treatments', 'label' => 'Tindakan', 'icon' => 'fa-notes-medical'],
    ];
@endphp

<div class="mb-6 flex gap-1 overflow-x-auto border-b border-slate-200 print:hidden">
    @foreach ($reportLinks as $link)
        <a href="{{ route($link['route']) }}"
            class="flex shrink-0 items-center gap-2 rounded-t-lg px-4 py-2.5 text-sm font-medium
                {{ request()->routeIs($link['route']) ? 'bg-blue-50 text-blue-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
            <i class="fa-solid {{ $link['icon'] }}"></i> {{ $link['label'] }}
        </a>
    @endforeach
</div>
