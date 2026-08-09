@php
    $clinic = \App\Models\SystemSetting::current();
@endphp
<div class="hidden print:mb-6 print:block">
    <div class="flex items-center gap-2">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-lg text-white" style="background-color: {{ $clinic->primary_color }}">
            @if ($clinic->logo_url)
                <img src="{{ $clinic->logo_url }}" alt="{{ $clinic->clinic_name }}" class="h-full w-full object-cover">
            @else
                <i class="fa-solid fa-tooth"></i>
            @endif
        </span>
        <span class="text-base font-bold uppercase">{{ $clinic->clinic_name }}</span>
    </div>
    @if ($clinic->address)
        <p class="mt-1 text-xs text-slate-500">{{ $clinic->address }}</p>
    @endif
    <h1 class="mt-3 text-lg font-bold">{{ $title }}</h1>
    @isset($periode)
        <p class="text-sm text-slate-600">Periode: {{ $periode }}</p>
    @endisset
</div>
