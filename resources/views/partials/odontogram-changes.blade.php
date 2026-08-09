@if ($changes->isEmpty())
    <div class="rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-8 text-center">
        <i class="fa-solid fa-check-circle mb-2 block text-2xl text-emerald-400"></i>
        <p class="text-sm text-slate-500">Tidak ada perubahan kondisi gigi antara kedua pemeriksaan.</p>
    </div>
@else
    <div class="divide-y divide-slate-100">
        @foreach ($changes as $change)
            <div class="flex flex-col gap-2 py-3 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm font-semibold text-slate-900">Gigi {{ $change['tooth_number'] }}</p>
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">{{ $change['before'] }}</span>
                    <i class="fa-solid fa-arrow-right-long text-xs text-slate-300"></i>
                    <span class="rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ $change['after'] }}</span>
                </div>
            </div>
        @endforeach
    </div>
@endif
