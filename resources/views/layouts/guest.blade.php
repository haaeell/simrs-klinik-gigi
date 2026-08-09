<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Klinik Gigi')</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col items-center justify-center bg-slate-50 px-4 py-10 antialiased">

    <div class="w-full @yield('container-class', 'max-w-md')">
        <div class="mb-8 flex flex-col items-center text-center">
            <span class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 text-2xl text-white shadow-sm">
                <i class="fa-solid fa-tooth"></i>
            </span>
            <h1 class="text-xl font-semibold text-slate-900">Klinik Gigi</h1>
            <p class="mt-1 text-sm text-slate-500">Sistem Antrean &amp; Rekam Medis</p>
        </div>

        @yield('content')

        <p class="mt-6 text-center text-xs text-slate-400">&copy; {{ date('Y') }} Klinik Gigi. Seluruh hak cipta dilindungi.</p>
    </div>

    @if (session('success'))
        <script>
            window.addEventListener('DOMContentLoaded', () => Swal.fire({
                icon: 'success', title: @json(session('success')),
                toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true,
            }));
        </script>
    @endif
</body>
</html>
