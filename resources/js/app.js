document.addEventListener('DOMContentLoaded', function () {
    // Sidebar drawer (mobile/tablet)
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const openBtn = document.getElementById('sidebar-open');
    const closeBtn = document.getElementById('sidebar-close');

    const openSidebar = () => {
        sidebar?.classList.remove('-translate-x-full');
        overlay?.classList.remove('hidden');
    };

    const closeSidebar = () => {
        sidebar?.classList.add('-translate-x-full');
        overlay?.classList.add('hidden');
    };

    openBtn?.addEventListener('click', openSidebar);
    closeBtn?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);

    // User dropdown menu
    const userMenuButton = document.getElementById('user-menu-button');
    const userMenu = document.getElementById('user-menu');

    userMenuButton?.addEventListener('click', function (event) {
        event.stopPropagation();
        userMenu?.classList.toggle('hidden');
    });

    document.addEventListener('click', function (event) {
        if (userMenu && !userMenu.classList.contains('hidden') && !userMenu.contains(event.target)) {
            userMenu.classList.add('hidden');
        }
    });

    // Generic tab switcher. Usage:
    // <div data-tabs> <button data-tab-target="key"> ... <div data-tab-panel="key">
    // Deep-links via ?tab=key so redirects (e.g. after a form save) can land on the right tab.
    document.querySelectorAll('[data-tabs]').forEach(function (group) {
        const buttons = [...group.querySelectorAll('[data-tab-target]')];
        const panels = [...group.querySelectorAll('[data-tab-panel]')];
        const activeClasses = (group.dataset.tabActiveClass || 'bg-blue-50 text-blue-700').split(' ');
        const inactiveClasses = (group.dataset.tabInactiveClass || 'text-slate-500 hover:bg-slate-50 hover:text-slate-900').split(' ');

        function activate(key) {
            buttons.forEach((btn) => {
                const isActive = btn.dataset.tabTarget === key;
                btn.classList.toggle('is-active', isActive);
                activeClasses.forEach((c) => btn.classList.toggle(c, isActive));
                inactiveClasses.forEach((c) => btn.classList.toggle(c, !isActive));
            });
            panels.forEach((panel) => panel.classList.toggle('hidden', panel.dataset.tabPanel !== key));
        }

        buttons.forEach((btn) => {
            btn.addEventListener('click', () => {
                activate(btn.dataset.tabTarget);
                const url = new URL(window.location.href);
                url.searchParams.set('tab', btn.dataset.tabTarget);
                window.history.replaceState({}, '', url);
            });
        });

        const requested = new URLSearchParams(window.location.search).get('tab');
        const validKeys = buttons.map((b) => b.dataset.tabTarget);
        activate(validKeys.includes(requested) ? requested : (validKeys[0] || ''));
    });
});

// CSRF token for jQuery AJAX calls (queue actions, display polling, odontogram, dsb.)
if (window.jQuery) {
    jQuery.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        },
    });
}

// Generic SweetAlert confirmation for destructive/important actions.
// Usage: <form data-confirm data-confirm-title="..." data-confirm-text="...">
document.addEventListener('submit', function (event) {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-confirm')) {
        return;
    }

    event.preventDefault();

    Swal.fire({
        title: form.dataset.confirmTitle || 'Anda yakin?',
        text: form.dataset.confirmText || 'Tindakan ini tidak dapat dibatalkan.',
        icon: form.dataset.confirmIcon || 'warning',
        showCancelButton: true,
        confirmButtonText: form.dataset.confirmButton || 'Ya, lanjutkan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});

// Queue actions (Panggil / Panggil Ulang / Lewati) — AJAX so the queue page never fully reloads.
// Usage: <button data-queue-action data-url="..."> inside <tr data-queue-row="ID">.
// Server responds with { queue: {status, status_label, status_badge_class}, counts: {...} }.
if (window.jQuery) jQuery(function ($) {
    $(document).on('click', '[data-queue-action]', function () {
        const $btn = $(this);
        const url = $btn.data('url');
        const $row = $btn.closest('tr[data-queue-row]');

        function run() {
            $btn.prop('disabled', true).css('opacity', 0.6);

            $.post(url)
                .done(function (res) {
                    const q = res.queue;

                    $row.find('[data-status-badge]')
                        .attr('class', 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' + q.status_badge_class)
                        .text(q.status_label);

                    $row.find('[data-status-actions]').addClass('hidden');
                    $row.find('[data-status-actions="' + q.status + '"]').removeClass('hidden');

                    $.each(res.counts, function (key, value) {
                        $('[data-count="' + key + '"]').text(value);
                    });
                })
                .fail(function (xhr) {
                    Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan, silakan coba lagi.', 'error');
                })
                .always(function () {
                    $btn.prop('disabled', false).css('opacity', 1);
                });
        }

        if ($btn.data('confirmText')) {
            Swal.fire({
                title: 'Lewati Antrean?',
                text: $btn.data('confirmText'),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, lewati',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) run();
            });
        } else {
            run();
        }
    });
});

// Odontogram: interactive FDI tooth grid + condition/surface modal (visits/show.blade.php).
// Grid markup: partials/odontogram-grid.blade.php. Each tooth carries its current
// state in data-condition / data-surfaces / data-notes so the modal can pre-fill on open.
document.addEventListener('DOMContentLoaded', function () {
    const grid = document.querySelector('[data-odontogram-grid]');
    const modal = document.getElementById('tooth-modal');
    if (!grid || !modal || !window.jQuery) return;

    const saveUrl = grid.dataset.saveUrl;
    const numberEl = document.getElementById('tooth-modal-number');
    const notesEl = document.getElementById('tooth-modal-notes');
    const conditionButtons = [...modal.querySelectorAll('[data-condition-option]')];
    const surfaceButtons = [...modal.querySelectorAll('[data-surface-option]')];
    const surfaceOrder = ['M', 'O', 'D', 'V', 'L'];

    let currentToothEl = null;
    let selectedCondition = null;
    let selectedSurfaces = new Set();

    function paintOptionButton(btn, active) {
        btn.classList.toggle('bg-blue-600', active);
        btn.classList.toggle('text-white', active);
        btn.classList.toggle('border-blue-600', active);
        btn.classList.toggle('border-slate-300', !active);
        btn.classList.toggle('text-slate-600', !active);
    }

    function renderConditionButtons() {
        conditionButtons.forEach((btn) => paintOptionButton(btn, btn.dataset.conditionOption === selectedCondition));
    }

    function renderSurfaceButtons() {
        surfaceButtons.forEach((btn) => paintOptionButton(btn, selectedSurfaces.has(btn.dataset.surfaceOption)));
    }

    function openModal(toothEl) {
        currentToothEl = toothEl;
        numberEl.textContent = toothEl.dataset.toothNumber;
        selectedCondition = toothEl.dataset.condition || null;
        selectedSurfaces = new Set((toothEl.dataset.surfaces || '').split('').filter(Boolean));
        notesEl.value = toothEl.dataset.notes || '';

        renderConditionButtons();
        renderSurfaceButtons();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        currentToothEl = null;
    }

    // Keep the "notable teeth" summary list (below the grid) in sync with AJAX saves —
    // it's server-rendered on load, so without this it goes stale within the same session.
    function syncNotableList(tooth) {
        const list = document.querySelector('[data-notable-list]');
        const emptyMsg = document.querySelector('[data-notable-empty]');
        if (!list) return;

        let item = list.querySelector(`[data-notable-item="${tooth.tooth_number}"]`);

        if (tooth.has_condition) {
            if (!item) {
                item = document.createElement('div');
                item.className = 'py-2 text-sm';
                item.dataset.notableItem = tooth.tooth_number;
                item.innerHTML = `<span class="font-semibold text-slate-900">Gigi ${tooth.tooth_number}</span> `
                    + '<span class="text-slate-500" data-notable-text></span>'
                    + '<p class="text-xs text-slate-400" data-notable-notes></p>';
                list.appendChild(item);
            }
            item.querySelector('[data-notable-text]').textContent = `— ${tooth.condition}${tooth.surfaces ? ` (${tooth.surfaces})` : ''}`;
            const notesEl2 = item.querySelector('[data-notable-notes]');
            notesEl2.textContent = tooth.notes || '';
            notesEl2.classList.toggle('hidden', !tooth.notes);
        } else if (item) {
            item.remove();
        }

        const hasAny = list.children.length > 0;
        list.classList.toggle('hidden', !hasAny);
        emptyMsg?.classList.toggle('hidden', hasAny);
    }

    grid.querySelectorAll('[data-tooth-btn]').forEach((el) => {
        el.addEventListener('click', () => openModal(el));
        el.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openModal(el);
            }
        });
    });

    conditionButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            selectedCondition = selectedCondition === btn.dataset.conditionOption ? null : btn.dataset.conditionOption;
            renderConditionButtons();
        });
    });

    surfaceButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const value = btn.dataset.surfaceOption;
            if (selectedSurfaces.has(value)) {
                selectedSurfaces.delete(value);
            } else {
                selectedSurfaces.add(value);
            }
            renderSurfaceButtons();
        });
    });

    document.getElementById('tooth-modal-close')?.addEventListener('click', closeModal);
    document.getElementById('tooth-modal-cancel')?.addEventListener('click', closeModal);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    document.getElementById('tooth-modal-save')?.addEventListener('click', function () {
        if (!currentToothEl) return;

        const toothEl = currentToothEl;
        const payload = {
            tooth_number: toothEl.dataset.toothNumber,
            condition: selectedCondition,
            surfaces: surfaceOrder.filter((s) => selectedSurfaces.has(s)),
            notes: notesEl.value,
        };

        jQuery.post(saveUrl, payload)
            .done(function (res) {
                const tooth = res.tooth;
                toothEl.dataset.condition = tooth.condition || '';
                toothEl.dataset.surfaces = tooth.surfaces || '';
                toothEl.dataset.notes = tooth.notes || '';
                toothEl.title = tooth.condition ? tooth.condition + (tooth.surfaces ? ` (${tooth.surfaces})` : '') : 'Normal';

                toothEl.classList.toggle('border-blue-300', tooth.has_condition);
                toothEl.classList.toggle('bg-blue-50', tooth.has_condition);
                toothEl.classList.toggle('text-blue-700', tooth.has_condition);
                toothEl.classList.toggle('border-slate-200', !tooth.has_condition);
                toothEl.classList.toggle('text-slate-500', !tooth.has_condition);

                const dot = toothEl.querySelector('span:last-child');
                if (dot) {
                    dot.classList.toggle('bg-blue-500', tooth.has_condition);
                    dot.classList.toggle('bg-transparent', !tooth.has_condition);
                }

                syncNotableList(tooth);
                closeModal();
                Swal.fire({
                    icon: 'success', title: `Gigi ${tooth.tooth_number} disimpan`,
                    toast: true, position: 'top-end', showConfirmButton: false, timer: 1800,
                });
            })
            .fail(function (xhr) {
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Gagal menyimpan kondisi gigi.', 'error');
            });
    });
});
