@extends('layouts.auth')

@section('title', 'Masuk Admin & Staff')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <h2 class="text-2xl font-extrabold text-slate-900">Masuk</h2>
        <p class="mt-1 text-sm text-slate-500">Gunakan email atau nomor telepon staff/admin.</p>

        <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5">
            @csrf

            <div>
                <label for="login" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Email / Telepon</label>
                <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus placeholder="admin@optik.com"
                       class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/25">
                @error('login')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Password</label>
                <input id="password" name="password" type="password" required placeholder="••••••••"
                       class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/25">
                @error('password')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-500">
                <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 bg-white text-red-600 focus:ring-red-600/25">
                Ingat saya
            </label>

            <button type="submit" class="w-full rounded-xl bg-red-600 py-3 text-sm font-bold text-white transition hover:bg-red-700">
                Masuk ke Dashboard
            </button>
        </form>

        <div class="my-6 flex items-center gap-3 text-xs text-slate-500">
            <span class="h-px flex-1 bg-slate-200"></span> ATAU <span class="h-px flex-1 bg-slate-200"></span>
        </div>

        <a href="{{ route('login.otp') }}" class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">
            <i data-lucide="message-circle" class="h-4 w-4 text-red-600"></i>
            Masuk sebagai Customer (WhatsApp OTP)
        </a>
    </div>
@endsection
