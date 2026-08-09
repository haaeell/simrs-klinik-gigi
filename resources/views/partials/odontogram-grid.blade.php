@php
    $teethByNumber = $teethByNumber ?? collect();
    $interactive = $interactive ?? false;
    $saveUrl = $saveUrl ?? null;

    $normalizeCondition = function (?string $condition) {
        return match ($condition) {
            'Fraktur' => 'Gigi Patah',
            'Crown' => 'Mahkota / Crown',
            'Un-erupted', 'Partial Erupted' => 'Belum Erupsi',
            'Implant', 'Anomali', 'Non Vital' => 'Lainnya',
            default => $condition ?: 'Normal',
        };
    };

    $conditionClasses = [
        'Normal' => ['tooth' => 'odontogram-tooth--normal', 'dot' => 'bg-white ring-1 ring-slate-300'],
        'Karies' => ['tooth' => 'odontogram-tooth--karies', 'dot' => 'bg-red-500'],
        'Tambalan' => ['tooth' => 'odontogram-tooth--tambalan', 'dot' => 'bg-blue-600'],
        'Gigi Hilang' => ['tooth' => 'odontogram-tooth--hilang', 'dot' => 'bg-slate-500'],
        'Perawatan Saluran Akar' => ['tooth' => 'odontogram-tooth--akar', 'dot' => 'bg-emerald-500'],
        'Mahkota / Crown' => ['tooth' => 'odontogram-tooth--crown', 'dot' => 'bg-violet-500'],
        'Gigi Patah' => ['tooth' => 'odontogram-tooth--patah', 'dot' => 'bg-orange-500'],
        'Sisa Akar' => ['tooth' => 'odontogram-tooth--sisa-akar', 'dot' => 'bg-amber-800'],
        'Belum Erupsi' => ['tooth' => 'odontogram-tooth--belum-erupsi', 'dot' => 'bg-rose-300'],
        'Lainnya' => ['tooth' => 'odontogram-tooth--lainnya', 'dot' => 'bg-teal-500'],
    ];

    $rows = [
        ['numbers' => \App\Models\Odontogram::UPPER_TEETH, 'position' => 'upper'],
        ['numbers' => \App\Models\Odontogram::LOWER_TEETH, 'position' => 'lower'],
    ];
@endphp

<div class="odontogram-board overflow-hidden py-1" @if ($interactive) data-odontogram-grid data-save-url="{{ $saveUrl }}" @endif>
    <div class="w-full space-y-7 px-2 py-3">
        @foreach ($rows as $row)
            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-4">
                @foreach (array_chunk($row['numbers'], 8) as $chunkIndex => $numbers)
                    <div class="grid grid-cols-8 gap-1.5 xl:gap-2">
                        @foreach ($numbers as $number)
                            @php
                                $tooth = $teethByNumber[$number] ?? null;
                                $condition = $normalizeCondition($tooth->condition ?? null);
                                $hasCondition = $condition !== 'Normal';
                                $stateClass = $conditionClasses[$condition]['tooth'] ?? $conditionClasses['Lainnya']['tooth'];
                                $dotClass = $conditionClasses[$condition]['dot'] ?? $conditionClasses['Lainnya']['dot'];
                            @endphp
                            <button
                                type="button"
                                @if (! $interactive) disabled @else data-tooth-btn @endif
                                data-tooth-number="{{ $number }}"
                                data-condition="{{ $tooth->condition ?? '' }}"
                                data-surfaces="{{ $tooth->surfaces ?? '' }}"
                                data-notes="{{ $tooth->notes ?? '' }}"
                                title="{{ $tooth && $tooth->condition ? $condition . ($tooth->surfaces ? " ({$tooth->surfaces})" : '') : 'Normal' }}"
                                class="odontogram-tooth group {{ $row['position'] === 'lower' ? 'odontogram-tooth--lower' : '' }} {{ $stateClass }} {{ $hasCondition ? 'is-marked' : '' }} {{ $interactive ? 'cursor-pointer hover:-translate-y-0.5 hover:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-200' : 'cursor-default' }}">
                                <span class="odontogram-tooth__number {{ $row['position'] === 'lower' ? 'order-2 mt-2' : 'mb-2' }}">{{ $number }}</span>
                                <span class="odontogram-tooth__shape" aria-hidden="true">
                                    <svg class="odontogram-tooth__svg" viewBox="0 0 64 92" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path class="odontogram-tooth__outer" d="M17 7C11.8 7 7.9 11.6 7 19.2C6.2 26.8 6.2 39.5 8.5 47.2C10 52.4 13.5 56.2 17.2 57.2C18.2 68 19.6 84.6 26.4 85.2C31.2 85.7 30.2 61 32 59.4C33.8 61 32.8 85.7 37.6 85.2C44.4 84.6 45.8 68 46.8 57.2C50.5 56.2 54 52.4 55.5 47.2C57.8 39.5 57.8 26.8 57 19.2C56.1 11.6 52.2 7 47 7C40.8 7 37.7 11.1 32 11.1C26.3 11.1 23.2 7 17 7Z" />
                                        <path class="odontogram-tooth__inner" d="M18.4 18.2C16.6 26.7 17.1 41.7 22.2 51.8" />
                                        <path class="odontogram-tooth__inner" d="M45.6 18.2C47.4 26.7 46.9 41.7 41.8 51.8" />
                                        <path class="odontogram-tooth__inner" d="M25.2 39.8C27.5 48.6 30.3 56.5 32 59.2C33.7 56.5 36.5 48.6 38.8 39.8" />
                                        <path class="odontogram-tooth__inner odontogram-tooth__gumline" d="M13 51.8C19.8 55.6 26.4 55.3 32 52C37.6 55.3 44.2 55.6 51 51.8" />
                                    </svg>
                                    <span class="odontogram-tooth__mark"></span>
                                    <span class="odontogram-tooth__spot {{ $dotClass }}"></span>
                                </span>
                                <span class="sr-only">Gigi {{ $number }}: {{ $condition }}</span>
                            </button>
                        @endforeach
                    </div>

                    @if ($chunkIndex === 0)
                        <div class="h-20 w-px bg-slate-200"></div>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>
</div>
