@extends('layouts.app')

@section('title', 'Pengaturan Layar Antrean')
@section('page-title', 'Pengaturan Layar Antrean')
@section('page-description', 'Atur kalimat pengumuman, nada dering, dan suara panggilan')

@section('content')
    <div class="mb-4">
        <a href="{{ route('queues.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-700">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Antrean
        </a>
    </div>

    <form method="POST" action="{{ route('queues.display-settings.update') }}" class="max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="mb-1 text-sm font-semibold text-slate-900">Kalimat Pengumuman</h3>
            <p class="mb-4 text-xs text-slate-400">
                Gunakan placeholder <code class="rounded bg-slate-100 px-1 py-0.5">{letter}</code>,
                <code class="rounded bg-slate-100 px-1 py-0.5">{number}</code>, dan
                <code class="rounded bg-slate-100 px-1 py-0.5">{patient_name}</code>.
            </p>
            <textarea name="announcement_template" id="announcement_template" rows="3"
                class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">{{ old('announcement_template', $setting->announcement_template) }}</textarea>
            @error('announcement_template')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-slate-900">Nada Dering</h3>
            <select name="chime_style" id="chime_style"
                class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                @foreach (\App\Models\DisplaySetting::CHIME_STYLES as $key => $label)
                    <option value="{{ $key }}" {{ old('chime_style', $setting->chime_style) === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="button" id="test-chime" class="mt-3 inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
                <i class="fa-solid fa-play"></i> Uji Coba Nada
            </button>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="mb-1 text-sm font-semibold text-slate-900">Suara Panggilan</h3>
            <p class="mb-4 text-xs text-slate-400">
                Daftar suara diambil dari browser yang membuka layar ini. Pilih suara di komputer/perangkat yang sama dengan yang dipakai untuk menampilkan Layar Antrean, supaya suaranya sesuai.
            </p>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="voice_name" class="mb-1.5 block text-sm font-medium text-slate-700">Suara</label>
                    <select name="voice_name" id="voice_name"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        <option value="">(Default browser)</option>
                    </select>
                    <input type="hidden" name="voice_lang" id="voice_lang" value="{{ old('voice_lang', $setting->voice_lang) }}">
                    <p class="mt-1.5 text-xs text-slate-400">Memuat daftar suara dari browser ini&hellip;</p>
                </div>

                <div>
                    <label for="voice_rate" class="mb-1.5 block text-sm font-medium text-slate-700">Kecepatan Bicara ({{ old('voice_rate', $setting->voice_rate) }}x)</label>
                    <input type="range" name="voice_rate" id="voice_rate" min="0.5" max="2" step="0.05"
                        value="{{ old('voice_rate', $setting->voice_rate) }}" class="w-full">
                </div>
                <div>
                    <label for="voice_pitch" class="mb-1.5 block text-sm font-medium text-slate-700">Nada Suara ({{ old('voice_pitch', $setting->voice_pitch) }})</label>
                    <input type="range" name="voice_pitch" id="voice_pitch" min="0" max="2" step="0.05"
                        value="{{ old('voice_pitch', $setting->voice_pitch) }}" class="w-full">
                </div>
            </div>

            <button type="button" id="test-voice" class="mt-4 inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
                <i class="fa-solid fa-play"></i> Uji Coba Suara
            </button>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Pengaturan
            </button>
        </div>
    </form>

    <script>
        const savedVoiceName = @json(old('voice_name', $setting->voice_name));

        function chimeFrequencies(style) {
            return {
                'ding-dong': [880, 1108],
                'bell': [1046],
                'double-beep': [1200, 1200],
            }[style] || [880, 1108];
        }

        function playTestChime() {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const now = ctx.currentTime;
            chimeFrequencies(document.getElementById('chime_style').value).forEach((freq, i) => {
                const start = now + i * 0.32;
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.type = 'sine';
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0.0001, start);
                gain.gain.exponentialRampToValueAtTime(0.35, start + 0.05);
                gain.gain.exponentialRampToValueAtTime(0.0001, start + 0.4);
                osc.connect(gain).connect(ctx.destination);
                osc.start(start);
                osc.stop(start + 0.4);
            });
        }

        document.getElementById('test-chime').addEventListener('click', playTestChime);

        function populateVoices() {
            if (!('speechSynthesis' in window)) return;

            const select = document.getElementById('voice_name');
            const voices = speechSynthesis.getVoices();
            if (voices.length === 0) return;

            select.innerHTML = '<option value="">(Default browser)</option>' + voices
                .map((voice) => `<option value="${voice.name}" data-lang="${voice.lang}">${voice.name} (${voice.lang})</option>`)
                .join('');

            if (savedVoiceName) {
                select.value = savedVoiceName;
            }

            document.querySelector('#voice_name + input + p').classList.add('hidden');
        }

        speechSynthesis?.addEventListener('voiceschanged', populateVoices);
        populateVoices();

        document.getElementById('voice_name').addEventListener('change', function () {
            const option = this.selectedOptions[0];
            document.getElementById('voice_lang').value = option?.dataset.lang || 'id-ID';
        });

        document.getElementById('voice_rate').addEventListener('input', function () {
            this.previousElementSibling.textContent = this.previousElementSibling.textContent.replace(/\([\d.]+x\)/, `(${this.value}x)`);
        });

        document.getElementById('voice_pitch').addEventListener('input', function () {
            this.previousElementSibling.textContent = this.previousElementSibling.textContent.replace(/\([\d.]+\)/, `(${this.value})`);
        });

        document.getElementById('test-voice').addEventListener('click', function () {
            if (!('speechSynthesis' in window)) return;

            const utterance = new SpeechSynthesisUtterance(
                document.getElementById('announcement_template').value
                    .replace('{letter}', 'A')
                    .replace('{number}', '1')
                    .replace('{patient_name}', 'Budi Santoso')
            );
            const chosenName = document.getElementById('voice_name').value;
            const voice = speechSynthesis.getVoices().find((v) => v.name === chosenName);
            if (voice) {
                utterance.voice = voice;
            }
            utterance.lang = document.getElementById('voice_lang').value || 'id-ID';
            utterance.rate = parseFloat(document.getElementById('voice_rate').value);
            utterance.pitch = parseFloat(document.getElementById('voice_pitch').value);

            speechSynthesis.cancel();
            speechSynthesis.speak(utterance);
        });
    </script>
@endsection
