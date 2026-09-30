<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name', 'Optik ERP') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                    colors: { ink: { 950: '#020617', 900: '#0f172a' } },
                },
            },
        };
    </script>
    <style>
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        ::-webkit-scrollbar-track { background: transparent; }
        [x-cloak] { display: none !important; }
    </style>
    @stack('head')
</head>
<body class="font-sans bg-slate-100 text-slate-800 antialiased">
<div class="min-h-screen" x-data="{ drawer: false }" @keydown.escape.window="drawer = false">

    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-40 w-64 border-r border-slate-200 bg-white text-slate-600 transition-transform duration-200 lg:translate-x-0"
           :class="drawer ? 'translate-x-0' : '-translate-x-full'">
        <div class="flex h-16 items-center gap-3 border-b border-slate-200 px-6">
            <img src="{{ asset('images/logo.jpg') }}" alt="Optik ERP" class="h-9 w-9 shrink-0 rounded-xl">
            <div>
                <p class="text-sm font-extrabold tracking-wide text-slate-900">OPTIK ERP</p>
                <p class="text-[11px] text-slate-500">Management Suite</p>
            </div>
        </div>

        <nav class="space-y-6 overflow-y-auto px-3 pb-28 pt-6" style="max-height: calc(100vh - 4rem);">
            @php
                $isAdmin = auth()->user()?->isAdmin();
                $group = fn (string $label) => '<p class="px-3 mb-2 text-[10px] font-bold uppercase tracking-[0.15em] text-slate-400">'.$label.'</p>';
                $item = function (string $route, string $icon, string $label, array $alsoActiveFor = []) {
                    $active = collect([$route, ...$alsoActiveFor])->contains(fn (string $target) =>
                        str_starts_with(request()->route()?->getName() ?? '', explode('.', $target)[0].'.')
                        || request()->routeIs($target)
                    );
                    return '<a href="'.route($route).'" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition '.($active ? 'bg-red-50 text-red-600 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900').'">
                        <i data-lucide="'.$icon.'" class="h-4 w-4 '.($active ? 'text-red-600' : 'text-slate-400').'"></i>
                        <span>'.$label.'</span>
                    </a>';
                };
            @endphp

            {!! $group('Utama') !!}
            @if (! auth()->user()->isCustomer())
                {!! $item('dashboard', 'layout-dashboard', 'Dashboard') !!}
                {!! $item('transactions.index', 'receipt-text', 'Transaksi') !!}
                {!! $item('customers.index', 'users', 'Customer') !!}
                {!! $item('prescriptions.index', 'file-heart', 'Resep') !!}
            @endif

            @if ($isAdmin)
                {!! $group('Master') !!}
                {!! $item('items.index', 'package', 'Master Item', ['frames.index', 'lenses.index', 'categories.index']) !!}
                {!! $group('Lainnya') !!}
                {!! $item('promotions.index', 'badge-percent', 'Promo') !!}
                {!! $item('reports.index', 'bar-chart-3', 'Sales Report') !!}
            @endif
        </nav>

        <div class="absolute inset-x-0 bottom-0 border-t border-slate-200 bg-white p-4">
            <div class="flex items-center gap-3">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-red-600 text-xs font-bold text-white">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                    <p class="text-[11px] uppercase tracking-wider text-red-600">{{ auth()->user()->role }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-600" title="Keluar">
                        <i data-lucide="log-out" class="h-4 w-4"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div x-cloak class="fixed inset-0 z-30 bg-slate-950/50 backdrop-blur-sm lg:hidden" x-show="drawer" @click="drawer = false"></div>

    <!-- Main -->
    <div class="lg:pl-64">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-slate-200 bg-white/80 px-4 backdrop-blur sm:px-6">
            <button type="button" @click="drawer = !drawer" class="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden">
                <i data-lucide="menu" class="h-5 w-5"></i>
            </button>

            <div class="min-w-0 flex-1">
                <h1 class="truncate text-base font-bold text-slate-900 sm:text-lg">@yield('title', 'Dashboard')</h1>
                <p class="hidden text-xs text-slate-500 sm:block">@yield('subtitle', '')</p>
            </div>

            <div class="hidden items-center gap-2 rounded-full border border-red-100 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 sm:flex">
                <span class="h-2 w-2 rounded-full bg-red-600"></span>
                {{ auth()->user()->store?->name ?? 'Toko Pusat' }}
            </div>

            <span class="grid h-9 w-9 place-items-center rounded-full bg-red-600 text-xs font-bold text-white">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </span>
        </header>

        <main class="p-4 sm:p-6 lg:p-8">
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>document.addEventListener('DOMContentLoaded', () => window.lucide && lucide.createIcons());</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@stack('scripts')
</body>
</html>
