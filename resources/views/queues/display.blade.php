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
        <div id="idle-state" class="{{ $current ? 'hidden' : '' }}">
            <i class="fa-solid fa-bell-slash mb-6 block text-6xl text-slate-300"></i>
            <p class="text-2xl font-medium text-slate-400">Menunggu pemanggilan antrean berikutnya&hellip;</p>
        </div>

        <div id="current-state" class="{{ $current ? '' : 'hidden' }}">
            <p class="text-xl font-semibold uppercase tracking-[0.3em] text-slate-400">Sedang Dipanggil</p>
            <p id="current-number" class="mt-4 text-[9rem] font-bold leading-none text-blue-600 sm:text-[11rem]">{{ $current['queue_number'] ?? '' }}</p>
            <p id="current-name" class="mt-4 text-3xl font-semibold uppercase tracking-wide text-slate-900 sm:text-4xl">{{ $current['patient_name'] ?? '' }}</p>
            <p class="mt-6 text-xl text-slate-500">Silakan menuju ruang pemeriksaan</p>
        </div>
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
            if (!audioCtx) return;

            const now = audioCtx.currentTime;
            chimeFrequencies(currentSettings.chime_style).forEach((freq, i) => {
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
        }

        // Speaks the announcement text already rendered server-side from the saved template.
        function announceCall(text) {
            if (!('speechSynthesis' in window)) return;

            const utterance = new SpeechSynthesisUtterance(text);
            const voice = speechSynthesis.getVoices().find((v) => v.name === currentSettings.voice_name);
            if (voice) {
                utterance.voice = voice;
            }
            utterance.lang = currentSettings.voice_lang || 'id-ID';
            utterance.rate = currentSettings.voice_rate || 0.95;
            utterance.pitch = currentSettings.voice_pitch || 1;

            speechSynthesis.cancel();
            speechSynthesis.speak(utterance);
        }

        document.getElementById('enable-sound')?.addEventListener('click', function () {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            playChime();
            speechSynthesis?.speak(new SpeechSynthesisUtterance(''));
            this.remove();
        });

        function renderCurrent(current) {
            const idle = document.getElementById('idle-state');
            const state = document.getElementById('current-state');

            if (!current) {
                idle.classList.remove('hidden');
                state.classList.add('hidden');
                return;
            }

            idle.classList.add('hidden');
            state.classList.remove('hidden');
            document.getElementById('current-number').textContent = current.queue_number;
            document.getElementById('current-name').textContent = current.patient_name;
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

        let lastCalledAt = @json($current['called_at'] ?? null);

        async function pollQueueDisplay() {
            try {
                const response = await fetch('{{ route('queues.display-data') }}', { headers: { Accept: 'application/json' } });
                const data = await response.json();

                if (data.settings) {
                    currentSettings = data.settings;
                }

                const calledAt = data.current ? data.current.called_at : null;
                if (calledAt && calledAt !== lastCalledAt) {
                    playChime();
                    setTimeout(() => announceCall(data.current.announcement), 800);
                }
                lastCalledAt = calledAt;

                renderCurrent(data.current);
                renderNext(data.next);
            } catch (error) {
                // Silent — the next interval will retry.
            }
        }

        setInterval(pollQueueDisplay, 1000);
    </script>
</body>
</html>
