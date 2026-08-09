<div id="tooth-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-900">Gigi <span id="tooth-modal-number"></span></h3>
            <button type="button" id="tooth-modal-close" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="mb-4">
            <label class="mb-2 block text-xs font-medium text-slate-600">Kondisi</label>
            <div class="flex flex-wrap gap-1.5">
                @foreach (['Normal', 'Karies', 'Tambalan', 'Gigi Hilang', 'Perawatan Saluran Akar', 'Mahkota / Crown', 'Gigi Patah', 'Sisa Akar', 'Belum Erupsi', 'Lainnya'] as $condition)
                    <button type="button" data-condition-option="{{ $condition }}"
                        class="rounded-full border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
                        {{ $condition }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="mb-4">
            <label class="mb-2 block text-xs font-medium text-slate-600">Permukaan <span class="font-normal text-slate-400">(boleh lebih dari satu)</span></label>
            <div class="flex flex-wrap gap-1.5">
                @foreach (\App\Models\Odontogram::SURFACES as $surface)
                    <button type="button" data-surface-option="{{ $surface }}"
                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-300 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        {{ $surface }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="mb-5">
            <label for="tooth-modal-notes" class="mb-1.5 block text-xs font-medium text-slate-600">Catatan</label>
            <textarea id="tooth-modal-notes" rows="2"
                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"></textarea>
        </div>

        <div class="flex justify-end gap-2">
            <button type="button" id="tooth-modal-cancel" class="rounded-xl px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</button>
            <button type="button" id="tooth-modal-save"
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <i class="fa-solid fa-check"></i> Simpan
            </button>
        </div>
    </div>
</div>
