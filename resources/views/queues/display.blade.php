<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $clinic = \App\Models\SystemSetting::current();
    @endphp
    <title>Layar Antrean - {{ $clinic->clinic_name }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-slate-50 antialiased">

    <header class="flex items-center justify-center gap-3 border-b border-slate-200 bg-white py-5">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl text-lg text-white" style="background-color: {{ $clinic->primary_color }}">
            @if ($clinic->logo_url)
                <img src="{{ $clinic->logo_url }}" alt="{{ $clinic->clinic_name }}" class="h-full w-full object-cover">
            @else
                <i class="fa-solid fa-tooth"></i>
            @endif
        </span>
        <span class="text-xl font-semibold uppercase tracking-wide text-slate-900">{{ $clinic->clinic_name }}</span>
    </header>

    <main class="flex flex-1 flex-col items-center justify-center px-6 py-10 text-center">
        <div id="rooms-container" class="w-full"></div>
    </main>

    <footer class="border-t border-slate-200 bg-white px-6 py-8">
        <p class="mb-4 text-center text-sm font-semibold uppercase tracking-widest text-slate-400">Antrean Berikutnya</p>
        <div id="next-list" class="flex flex-wrap items-center justify-center gap-4">
            @forelse ($next as $number)
                <span class="rounded-2xl border border-slate-200 bg-slate-50 px-8 py-4 text-3xl font-bold text-slate-700">{{ $number }}</span>
            @empty
                <span class="text-sm text-slate-400">Tidak ada antrean menunggu.</span>
            @endforelse
        </div>
    </footer>

    <button id="enable-sound" type="button"
        class="fixed bottom-6 right-6 z-50 inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg hover:bg-blue-700">
        <i class="fa-solid fa-volume-high"></i> Aktifkan Suara Panggilan
    </button>

    <div class="fixed bottom-6 left-6 z-50 flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-lg">
        <div id="checkin-qr"></div>
        <div class="max-w-[9rem] text-left">
            <p class="text-xs font-semibold text-slate-900">Sudah tiba?</p>
            <p class="text-xs text-slate-500">Scan untuk check-in &amp; ambil antrean</p>
        </div>
    </div>

    <script>
        // Browsers block audio until a user interaction unlocks it — this TV display runs
        // unattended, so staff taps this once when setting up the screen.
        let audioCtx = null;
        let currentSettings = @json($settings ?? []);

        function chimeFrequencies(style) {
            return {
                'ding-dong': [880, 1108],
                'bell': [1046],
                'double-beep': [1200, 1200],
            }[style] || [880, 1108];
        }

        function playChime() {
            return new Promise((resolve) => {
                if (!audioCtx) return resolve();

                const now = audioCtx.currentTime;
                const freqs = chimeFrequencies(currentSettings.chime_style);
                freqs.forEach((freq, i) => {
                    const start = now + i * 0.32;
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.type = 'sine';
                    osc.frequency.value = freq;
                    gain.gain.setValueAtTime(0.0001, start);
                    gain.gain.exponentialRampToValueAtTime(0.35, start + 0.05);
                    gain.gain.exponentialRampToValueAtTime(0.0001, start + 0.4);
                    osc.connect(gain).connect(audioCtx.destination);
                    osc.start(start);
                    osc.stop(start + 0.4);
                });
                setTimeout(resolve, freqs.length * 320 + 400);
            });
        }

        // Speaks the announcement text already rendered server-side from the saved template.
        function speak(text) {
            return new Promise((resolve) => {
                if (!('speechSynthesis' in window)) return resolve();

                const utterance = new SpeechSynthesisUtterance(text);
                const voice = speechSynthesis.getVoices().find((v) => v.name === currentSettings.voice_name);
                if (voice) {
                    utterance.voice = voice;
                }
                utterance.lang = currentSettings.voice_lang || 'id-ID';
                utterance.rate = currentSettings.voice_rate || 0.95;
                utterance.pitch = currentSettings.voice_pitch || 1;
                utterance.onend = resolve;
                utterance.onerror = resolve;

                speechSynthesis.speak(utterance);
            });
        }

        // Multiple rooms can call at the same instant — announce them one after another
        // instead of talking over each other.
        let announceQueue = Promise.resolve();
        function announceCall(text) {
            announceQueue = announceQueue.then(() => playChime()).then(() => speak(text));
        }

        document.getElementById('enable-sound')?.addEventListener('click', function () {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            playChime();
            speechSynthesis?.speak(new SpeechSynthesisUtterance(''));
            this.remove();
        });

        function roomCardHtml(room, big) {
            const numberSize = big ? 'text-[9rem] sm:text-[11rem]' : 'text-6xl sm:text-7xl';
            const nameSize = big ? 'text-3xl sm:text-4xl' : 'text-xl sm:text-2xl';

            if (!room.current) {
                return `
                    <div class="rounded-3xl border border-slate-200 bg-white p-8">
                        ${room.room_name ? `<p class="mb-3 text-sm font-semibold uppercase tracking-widest text-slate-400">${room.room_name}</p>` : ''}
                        <i class="fa-solid fa-bell-slash mb-4 block text-4xl text-slate-300"></i>
                        <p class="text-lg font-medium text-slate-400">Menunggu pemanggilan&hellip;</p>
                    </div>`;
            }

            return `
                <div class="rounded-3xl border border-blue-100 bg-white p-8">
                    ${room.room_name ? `<p class="mb-2 text-sm font-semibold uppercase tracking-widest text-slate-400">${room.room_name}</p>` : ''}
                    <p class="text-lg font-semibold uppercase tracking-[0.3em] text-slate-400">Sedang Dipanggil</p>
                    <p class="mt-3 ${numberSize} font-bold leading-none text-blue-600">${room.current.queue_number}</p>
                    <p class="mt-3 ${nameSize} font-semibold uppercase tracking-wide text-slate-900">${room.current.patient_name}</p>
                </div>`;
        }

        function renderRooms(rooms) {
            const container = document.getElementById('rooms-container');
            const big = rooms.length <= 1;

            container.className = big
                ? 'w-full'
                : 'grid w-full max-w-6xl grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3';

            container.innerHTML = rooms.map((room) => roomCardHtml(room, big)).join('');
        }

        function renderNext(list) {
            const container = document.getElementById('next-list');

            if (!list || list.length === 0) {
                container.innerHTML = '<span class="text-sm text-slate-400">Tidak ada antrean menunggu.</span>';
                return;
            }

            container.innerHTML = list
                .map((number) => `<span class="rounded-2xl border border-slate-200 bg-slate-50 px-8 py-4 text-3xl font-bold text-slate-700">${number}</span>`)
                .join('');
        }

        let lastCalledAt = @json(collect($rooms ?? [])->mapWithKeys(fn ($room) => [$room['room_id'] ?? 'default' => $room['current']['called_at'] ?? null]));

        async function pollQueueDisplay() {
            try {
                const response = await fetch('{{ route('queues.display-data') }}', { headers: { Accept: 'application/json' } });
                const data = await response.json();

                if (data.settings) {
                    currentSettings = data.settings;
                }

                (data.rooms || []).forEach((room) => {
                    const key = room.room_id ?? 'default';
                    const calledAt = room.current ? room.current.called_at : null;

                    if (calledAt && calledAt !== lastCalledAt[key]) {
                        announceCall(room.current.announcement);
                    }
                    lastCalledAt[key] = calledAt;
                });

                renderRooms(data.rooms || []);
                renderNext(data.next);
            } catch (error) {
                // Silent — the next interval will retry.
            }
        }

        renderRooms(@json($rooms ?? []));
        setInterval(pollQueueDisplay, 1000);

        // Static — the check-in URL never changes, so this only needs to render once.
        new QRCode(document.getElementById('checkin-qr'), {
            text: '{{ route('check-in') }}',
            width: 84,
            height: 84,
        });
    </script>
</body>
</html>
