<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $clinic = \App\Models\SystemSetting::current();
    @endphp
    <title>Masuk - {{ $clinic->clinic_name }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 px-4 antialiased">

    <div class="w-full max-w-md">
        <div class="mb-8 flex flex-col items-center text-center">
            <span class="mb-4 flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl text-2xl text-white shadow-sm" style="background-color: {{ $clinic->primary_color }}">
                @if ($clinic->logo_url)
                    <img src="{{ $clinic->logo_url }}" alt="{{ $clinic->clinic_name }}" class="h-full w-full object-cover">
                @else
                    <i class="fa-solid fa-tooth"></i>
                @endif
            </span>
            <h1 class="text-xl font-semibold text-slate-900">{{ $clinic->clinic_name }}</h1>
            <p class="mt-1 text-sm text-slate-500">Sistem Antrean &amp; Rekam Medis</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <h2 class="mb-1 text-lg font-semibold text-slate-900">Masuk ke Akun Anda</h2>
            <p class="mb-6 text-sm text-slate-500">Silakan masuk menggunakan akun yang telah didaftarkan admin.</p>

            @if ($errors->any())
                <div class="mb-5 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                    <div class="relative">
                        <i class="fa-solid fa-envelope pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full rounded-xl border border-slate-300 py-2.5 pl-10 pr-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            placeholder="nama@klinik.com">
                    </div>
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Kata Sandi</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="password" id="password" name="password" required
                            class="w-full rounded-xl border border-slate-300 py-2.5 pl-10 pr-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            placeholder="••••••••">
                    </div>
                </div>

                <label class="flex select-none items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    Ingat saya
                </label>

                <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-blue-700">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Masuk
                </button>
            </form>

            <p class="mt-5 text-center text-sm text-slate-500">
                Pasien belum punya akun? <a href="{{ route('register-patient.choose') }}" class="font-medium text-blue-600 hover:text-blue-700">Daftar di sini</a>
            </p>
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">&copy; {{ date('Y') }} {{ $clinic->clinic_name }}. Seluruh hak cipta dilindungi.</p>
    </div>

</body>
</html>
