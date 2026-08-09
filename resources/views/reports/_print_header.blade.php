<div class="hidden print:mb-6 print:block">
    <div class="flex items-center gap-2">
        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white">
            <i class="fa-solid fa-tooth"></i>
        </span>
        <span class="text-base font-bold">KLINIK GIGI</span>
    </div>
    <h1 class="mt-3 text-lg font-bold">{{ $title }}</h1>
    @isset($periode)
        <p class="text-sm text-slate-600">Periode: {{ $periode }}</p>
    @endisset
</div>
