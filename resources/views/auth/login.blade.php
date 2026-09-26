@extends('layouts.auth')

@section('title', 'Masuk Admin & Staff')

@section('content')
    <div class="rounded-2xl border border-white/10 bg-white/5 p-8 backdrop-blur">
        <h2 class="text-2xl font-extrabold text-white">Masuk</h2>
        <p class="mt-1 text-sm text-slate-400">Gunakan email atau nomor telepon staff/admin.</p>

        <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5">
            @csrf

            <div>
                <label for="login" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-400">Email / Telepon</label>
                <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus placeholder="admin@optik.com"
                       class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                @error('login')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-400">Password</label>
                <input id="password" name="password" type="password" required placeholder="••••••••"
                       class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                @error('password')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-400">
                <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-600 bg-slate-900 text-emerald-500 focus:ring-emerald-500/30">
                Ingat saya
            </label>

            <button type="submit" class="w-full rounded-xl bg-emerald-500 py-3 text-sm font-bold text-white transition hover:bg-emerald-400">
                Masuk ke Dashboard
            </button>
        </form>

        <div class="my-6 flex items-center gap-3 text-xs text-slate-500">
            <span class="h-px flex-1 bg-white/10"></span> ATAU <span class="h-px flex-1 bg-white/10"></span>
        </div>

        <a href="{{ route('login.otp') }}" class="flex w-full items-center justify-center gap-2 rounded-xl border border-white/15 py-3 text-sm font-semibold text-white transition hover:bg-white/5">
            <i data-lucide="message-circle" class="h-4 w-4 text-emerald-400"></i>
            Masuk sebagai Customer (WhatsApp OTP)
        </a>
    </div>
@endsection
