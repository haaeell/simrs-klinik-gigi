<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layar Antrean - Klinik Gigi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-slate-50 antialiased">

    <header class="flex items-center justify-center gap-3 border-b border-slate-200 bg-white py-5">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-lg text-white">
            <i class="fa-solid fa-tooth"></i>
        </span>
        <span class="text-xl font-semibold tracking-wide text-slate-900">KLINIK GIGI</span>
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

    <script>
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

        async function pollQueueDisplay() {
            try {
                const response = await fetch('{{ route('queues.display-data') }}', { headers: { Accept: 'application/json' } });
                const data = await response.json();
                renderCurrent(data.current);
                renderNext(data.next);
            } catch (error) {
                // Silent — the next interval will retry.
            }
        }

        setInterval(pollQueueDisplay, 5000);
    </script>
</body>
</html>
