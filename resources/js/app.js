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

    // Notification bell dropdown (patient portal)
    const notifMenuButton = document.getElementById('notif-menu-button');
    const notifMenu = document.getElementById('notif-menu');

    notifMenuButton?.addEventListener('click', function (event) {
        event.stopPropagation();
        notifMenu?.classList.toggle('hidden');
    });

    document.addEventListener('click', function (event) {
        if (notifMenu && !notifMenu.classList.contains('hidden') && !notifMenu.contains(event.target)) {
            notifMenu.classList.add('hidden');
        }
    });

    // "Perlu Kontrol?" checkbox toggle on the doctor exam form.
    document.querySelectorAll('[data-control-toggle]').forEach(function (wrapper) {
        const checkbox = wrapper.querySelector('[data-control-checkbox]');
        const fields = wrapper.querySelector('[data-control-fields]');
        checkbox?.addEventListener('change', function () {
            fields?.classList.toggle('hidden', !checkbox.checked);
        });
    });

    // Live search: <form data-live-search> auto-submits shortly after typing stops, so
    // list pages (Pasien, Rekam Medis, Pengguna) filter without needing Enter or a button.
    document.querySelectorAll('form[data-live-search]').forEach(function (form) {
        const input = form.querySelector('input[name="search"]');
        if (!input) return;

        let timer;
        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(() => form.submit(), 450);
        });
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

        function run(data) {
            $btn.prop('disabled', true).css('opacity', 0.6);

            $.post(url, data || {})
                .done(function (res) {
                    const q = res.queue;

                    $row.find('[data-status-badge]')
                        .attr('class', 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ' + q.status_badge_class)
                        .text(q.status_label);

                    $row.find('[data-status-actions]').addClass('hidden');
                    $row.find('[data-status-actions="' + q.status + '"]').removeClass('hidden');
                    $row.find('[data-room-name]').text(q.room_name || '-');

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

        // "Panggil" — the doctor/room is normally already chosen when the queue was booked
        // (see queues/create.blade.php), so this just calls straight away. Only ask here for
        // queues that never got a room (online/QR self check-in, or legacy walk-ins).
        // (Checked via attribute presence, not .data() truthiness — the attribute has no
        // value, i.e. data-call-action="", which jQuery reads as an empty string and thus falsy.)
        if ($btn.is('[data-call-action]')) {
            const rooms = window.activeRooms || [];
            const alreadyHasRoom = !!$btn.data('roomId');

            if (alreadyHasRoom || rooms.length === 0) {
                run();
            } else {
                Swal.fire({
                    title: 'Panggil ke Ruang Mana?',
                    text: 'Antrean ' + $btn.data('queueNumber'),
                    input: 'select',
                    inputOptions: rooms.reduce(function (opts, room) {
                        opts[room.id] = room.name + ' — ' + room.doctor_name;
                        return opts;
                    }, {}),
                    inputPlaceholder: 'Pilih ruangan',
                    showCancelButton: true,
                    confirmButtonText: 'Panggil',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#2563eb',
                    reverseButtons: true,
                    inputValidator: (value) => !value && 'Pilih ruangan terlebih dahulu.',
                }).then((result) => {
                    if (result.isConfirmed) run({ room_id: result.value });
                });
            }
            return;
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

// Odontogram: interactive FDI tooth grid + inline editor (visits/show.blade.php).
// Grid markup: partials/odontogram-grid.blade.php. Each tooth carries its current
// state in data-condition / data-surfaces / data-notes so the editor can pre-fill on click.
document.addEventListener('DOMContentLoaded', function () {
    const grid = document.querySelector('[data-odontogram-grid]');
    if (!grid || !window.jQuery) return;

    const saveUrl = grid.dataset.saveUrl;
    const inlineCondition = document.querySelector('[data-inline-condition]');
    const inlineSurfaces = [...document.querySelectorAll('[data-inline-surface]')];
    const inlineNotes = document.querySelector('[data-inline-notes]');
    const inlineSave = document.querySelector('[data-inline-save]');
    const inlineCancel = document.querySelector('[data-inline-cancel]');
    const selectedLabel = document.querySelector('[data-selected-tooth-label]');
    const selectedPreview = document.querySelector('[data-selected-tooth-preview]');
    const modal = document.getElementById('tooth-modal');
    const numberEl = document.getElementById('tooth-modal-number');
    const notesEl = document.getElementById('tooth-modal-notes');
    const conditionButtons = modal ? [...modal.querySelectorAll('[data-condition-option]')] : [];
    const surfaceButtons = modal ? [...modal.querySelectorAll('[data-surface-option]')] : [];
    const surfaceOrder = ['M', 'O', 'D', 'V', 'L'];
    const conditionClassMap = {
        'Normal': { tooth: 'odontogram-tooth--normal', dot: 'bg-white ring-1 ring-slate-300' },
        'Karies': { tooth: 'odontogram-tooth--karies', dot: 'bg-red-500' },
        'Tambalan': { tooth: 'odontogram-tooth--tambalan', dot: 'bg-blue-600' },
        'Gigi Hilang': { tooth: 'odontogram-tooth--hilang', dot: 'bg-slate-500' },
        'Perawatan Saluran Akar': { tooth: 'odontogram-tooth--akar', dot: 'bg-emerald-500' },
        'Mahkota / Crown': { tooth: 'odontogram-tooth--crown', dot: 'bg-violet-500' },
        'Gigi Patah': { tooth: 'odontogram-tooth--patah', dot: 'bg-orange-500' },
        'Sisa Akar': { tooth: 'odontogram-tooth--sisa-akar', dot: 'bg-amber-800' },
        'Belum Erupsi': { tooth: 'odontogram-tooth--belum-erupsi', dot: 'bg-rose-300' },
        'Lainnya': { tooth: 'odontogram-tooth--lainnya', dot: 'bg-teal-500' },
    };
    const conditionAliases = {
        'Fraktur': 'Gigi Patah',
        'Crown': 'Mahkota / Crown',
        'Un-erupted': 'Belum Erupsi',
        'Partial Erupted': 'Belum Erupsi',
        'Implant': 'Lainnya',
        'Anomali': 'Lainnya',
        'Non Vital': 'Lainnya',
    };

    let currentToothEl = null;
    let selectedCondition = null;
    let selectedSurfaces = new Set();

    function normalizeCondition(condition) {
        return conditionAliases[condition] || condition || 'Normal';
    }

    function conditionClasses(condition) {
        return conditionClassMap[normalizeCondition(condition)] || conditionClassMap.Lainnya;
    }

    function allToothStateClasses() {
        return Object.values(conditionClassMap).map((item) => item.tooth);
    }

    function allDotClasses() {
        return Object.values(conditionClassMap).flatMap((item) => item.dot.split(' '));
    }

    function paintTooth(toothEl, condition) {
        const normalized = normalizeCondition(condition);
        const hasCondition = normalized !== 'Normal';
        const classes = conditionClasses(normalized);

        toothEl.classList.remove(...allToothStateClasses());
        toothEl.classList.add(classes.tooth);
        toothEl.classList.toggle('is-marked', hasCondition);

        const dot = toothEl.querySelector('.odontogram-tooth__spot');
        if (dot) {
            dot.classList.remove(...allDotClasses());
            dot.classList.add(...classes.dot.split(' '));
        }
    }

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

    function fillInlineEditor(toothEl) {
        currentToothEl = toothEl;
        selectedCondition = normalizeCondition(toothEl.dataset.condition);
        selectedSurfaces = new Set((toothEl.dataset.surfaces || '').split('').filter(Boolean));
        selectedLabel && (selectedLabel.textContent = toothEl.dataset.toothNumber);

        if (inlineCondition) inlineCondition.value = selectedCondition;
        inlineSurfaces.forEach((input) => {
            input.checked = selectedSurfaces.has(input.value);
        });
        if (inlineNotes) inlineNotes.value = toothEl.dataset.notes || '';
        if (selectedPreview) paintTooth(selectedPreview, selectedCondition);
    }

    function openModal(toothEl) {
        if (inlineCondition && inlineNotes) {
            fillInlineEditor(toothEl);
            return;
        }

        if (!modal || !numberEl || !notesEl) return;

        currentToothEl = toothEl;
        numberEl.textContent = toothEl.dataset.toothNumber;
        selectedCondition = normalizeCondition(toothEl.dataset.condition);
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
                item.className = 'rounded-lg border border-slate-200 px-3 py-2 text-sm';
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

    const firstTooth = grid.querySelector('[data-tooth-btn]');
    if (firstTooth && inlineCondition && inlineNotes) {
        const preferredTooth = grid.querySelector('[data-tooth-number="16"]') || firstTooth;
        fillInlineEditor(preferredTooth);
    }

    inlineCondition?.addEventListener('change', () => {
        selectedCondition = inlineCondition.value;
        if (selectedPreview) paintTooth(selectedPreview, selectedCondition);
    });

    inlineSurfaces.forEach((input) => {
        input.addEventListener('change', () => {
            selectedSurfaces = new Set(inlineSurfaces.filter((surface) => surface.checked).map((surface) => surface.value));
        });
    });

    inlineCancel?.addEventListener('click', () => {
        if (currentToothEl) fillInlineEditor(currentToothEl);
    });

    conditionButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            selectedCondition = selectedCondition === btn.dataset.conditionOption ? 'Normal' : btn.dataset.conditionOption;
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
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });

    function saveCurrentTooth() {
        if (!currentToothEl) return;

        const toothEl = currentToothEl;
        const payload = {
            tooth_number: toothEl.dataset.toothNumber,
            condition: selectedCondition,
            surfaces: surfaceOrder.filter((s) => selectedSurfaces.has(s)),
            notes: inlineNotes ? inlineNotes.value : notesEl.value,
        };

        jQuery.post(saveUrl, payload)
            .done(function (res) {
                const tooth = res.tooth;
                toothEl.dataset.condition = tooth.condition || '';
                toothEl.dataset.surfaces = tooth.surfaces || '';
                toothEl.dataset.notes = tooth.notes || '';
                toothEl.title = tooth.condition ? tooth.condition + (tooth.surfaces ? ` (${tooth.surfaces})` : '') : 'Normal';

                paintTooth(toothEl, tooth.condition);
                if (selectedPreview) paintTooth(selectedPreview, tooth.condition);

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
    }

    inlineSave?.addEventListener('click', saveCurrentTooth);

    document.getElementById('tooth-modal-save')?.addEventListener('click', function () {
        saveCurrentTooth();
    });
});
