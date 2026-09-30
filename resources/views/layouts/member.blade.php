<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Member') · {{ config('app.name', 'Optik') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'] } } },
        };
    </script>
    @stack('head')
</head>
<body class="font-sans bg-slate-100 text-slate-800 antialiased min-h-screen pb-24">
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex h-14 max-w-lg items-center justify-between px-4">
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/logo.jpg') }}" alt="Optik" class="h-8 w-8 shrink-0 rounded-lg">
                <div>
                    <p class="text-sm font-extrabold leading-none text-slate-900">KARTU MEMBER</p>
                    <p class="text-[10px] text-slate-500">{{ auth()->user()?->customer?->member_id ?? 'Member' }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-1.5 rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600">
                    <i data-lucide="log-out" class="h-3.5 w-3.5"></i> Keluar
                </button>
            </form>
        </div>
    </header>

    <main class="mx-auto max-w-lg px-4 py-5">
        @include('partials.flash')
        @yield('content')
    </main>

    <!-- Bottom navigation -->
    <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto grid max-w-lg grid-cols-3">
            @php
                $navItems = [
                    ['route' => 'portal.index', 'icon' => 'layout-dashboard', 'label' => 'Dashboard'],
                    ['route' => 'portal.transactions', 'icon' => 'receipt-text', 'label' => 'Transaksi'],
                    ['route' => 'portal.promos', 'icon' => 'badge-percent', 'label' => 'Promo'],
                ];
            @endphp
            @foreach ($navItems as $nav)
                @php($active = request()->routeIs($nav['route'].'*'))
                <a href="{{ route($nav['route']) }}"
                   class="flex flex-col items-center gap-1 py-3 text-[11px] font-semibold {{ $active ? 'text-red-600' : 'text-slate-400' }}">
                    <i data-lucide="{{ $nav['icon'] }}" class="h-5 w-5"></i>
                    {{ $nav['label'] }}
                </a>
            @endforeach
        </div>
    </nav>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>document.addEventListener('DOMContentLoaded', () => window.lucide && lucide.createIcons());</script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')
</body>
</html>
