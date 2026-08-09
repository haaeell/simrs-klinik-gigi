<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Antrean {{ $queue->queue_number }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            @page { margin: 0; size: 80mm auto; }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 py-10 antialiased print:bg-white print:py-0">

    <div class="mx-auto mb-6 flex w-full max-w-xs items-center justify-between px-1 print:hidden">
        <a href="{{ route('queues.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">
            <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Antrean
        </a>
        <button type="button" onclick="window.print()"
            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
            <i class="fa-solid fa-print"></i> Cetak
        </button>
    </div>

    <div class="mx-auto w-full max-w-xs rounded-2xl border border-slate-200 bg-white p-6 font-mono text-sm text-slate-800 shadow-sm print:max-w-none print:rounded-none print:border-0 print:shadow-none">
        <p class="text-center">================================</p>
        <p class="py-1 text-center text-base font-bold tracking-wide">KLINIK GIGI</p>
        <p class="text-center">================================</p>

        <p class="mt-4 text-center text-xs tracking-widest">NOMOR ANTREAN</p>
        <p class="my-2 text-center text-4xl font-bold">{{ $queue->queue_number }}</p>

        <div class="mt-4 space-y-2.5">
            <div>
                <p class="text-xs text-slate-500">Nama:</p>
                <p class="font-semibold">{{ $queue->patient->name }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">No RM:</p>
                <p class="font-semibold">{{ $queue->patient->medical_record_number }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Tanggal:</p>
                <p class="font-semibold">{{ $queue->queue_date->translatedFormat('d F Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Jam:</p>
                <p class="font-semibold">{{ $queue->created_at->format('H:i') }}</p>
            </div>
        </div>

        <p class="mt-5 text-center text-xs leading-relaxed">
            Silakan menunggu nomor<br>Anda dipanggil.
        </p>
        <p class="mt-4 text-center">================================</p>
    </div>

</body>
</html>
