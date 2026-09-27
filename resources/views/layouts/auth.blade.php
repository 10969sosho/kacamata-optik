<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Masuk') · {{ config('app.name', 'Optik ERP') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'] } } },
        };
    </script>
</head>
<body class="font-sans bg-slate-100 text-slate-800 antialiased">
<div class="flex min-h-screen flex-col lg:flex-row">

    <div class="relative hidden flex-1 overflow-hidden bg-gradient-to-br from-red-600 via-red-700 to-slate-900 lg:block">
        <div class="absolute -left-24 top-1/4 h-96 w-96 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute -right-16 bottom-0 h-80 w-80 rounded-full bg-red-500/30 blur-3xl"></div>
        <div class="relative flex h-full flex-col justify-between p-12">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.svg') }}" alt="Optik ERP" class="h-10 w-10 rounded-xl">
                <span class="text-lg font-extrabold tracking-wide text-white">OPTIK ERP</span>
            </div>
            <div class="max-w-md">
                <h1 class="text-4xl font-extrabold leading-tight text-white">Kelola toko kacamata dalam satu tempat.</h1>
                <p class="mt-4 text-slate-400">POS, master frame & lensa, resep, member, sampai laporan penjualan — rapi dan cepat.</p>
            </div>
            <p class="text-xs text-white/70">&copy; {{ date('Y') }} Optik Management Suite</p>
        </div>
    </div>

    <div class="flex flex-1 items-center justify-center px-4 py-10 sm:px-8">
        <div class="w-full max-w-md">
            <div class="mb-8 flex items-center gap-3 lg:hidden">
                <img src="{{ asset('images/logo.svg') }}" alt="Optik ERP" class="h-10 w-10 rounded-xl">
                <span class="text-lg font-extrabold tracking-wide text-slate-900">OPTIK ERP</span>
            </div>

            @include('partials.flash')
            @yield('content')
        </div>
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>document.addEventListener('DOMContentLoaded', () => window.lucide && lucide.createIcons());</script>
</body>
</html>
