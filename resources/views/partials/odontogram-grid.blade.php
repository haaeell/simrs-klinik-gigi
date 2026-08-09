@php
    $teethByNumber = $teethByNumber ?? collect();
    $interactive = $interactive ?? false;
@endphp

<div class="flex flex-col items-center gap-3 overflow-x-auto py-1" @if ($interactive) data-odontogram-grid data-save-url="{{ $saveUrl }}" @endif>
    @foreach ([\App\Models\Odontogram::UPPER_TEETH, \App\Models\Odontogram::LOWER_TEETH] as $numbers)
        <div class="flex items-center gap-1 sm:gap-1.5">
            @foreach ($numbers as $i => $number)
                @if ($i === 8)
                    <div class="mx-1 h-9 w-px shrink-0 bg-slate-200 sm:mx-2 sm:h-10"></div>
                @endif
                @php
                    $tooth = $teethByNumber[$number] ?? null;
                    $hasCondition = $tooth && $tooth->condition && $tooth->condition !== 'Normal';
                @endphp
                <div
                    @if ($interactive) data-tooth-btn role="button" tabindex="0" @endif
                    data-tooth-number="{{ $number }}"
                    data-condition="{{ $tooth->condition ?? '' }}"
                    data-surfaces="{{ $tooth->surfaces ?? '' }}"
                    data-notes="{{ $tooth->notes ?? '' }}"
                    title="{{ $tooth && $tooth->condition ? $tooth->condition . ($tooth->surfaces ? " ({$tooth->surfaces})" : '') : 'Normal' }}"
                    class="flex h-11 w-9 shrink-0 select-none flex-col items-center justify-center gap-0.5 rounded-lg border text-[11px] font-semibold sm:h-12 sm:w-10
                        {{ $hasCondition ? 'border-blue-300 bg-blue-50 text-blue-700' : 'border-slate-200 bg-white text-slate-500' }}
                        {{ $interactive ? 'cursor-pointer hover:border-blue-400 hover:bg-blue-50' : '' }}">
                    <span>{{ $number }}</span>
                    <span class="block h-1.5 w-1.5 rounded-full {{ $hasCondition ? 'bg-blue-500' : 'bg-transparent' }}"></span>
                </div>
            @endforeach
        </div>
    @endforeach
</div>
