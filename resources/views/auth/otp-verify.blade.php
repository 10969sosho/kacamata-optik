@extends('layouts.auth')

@section('title', 'Verifikasi OTP')

@section('content')
    <div class="rounded-2xl border border-white/10 bg-white/5 p-8 backdrop-blur">
        <h2 class="text-2xl font-extrabold text-white">Verifikasi Kode</h2>
        <p class="mt-1 text-sm text-slate-400">
            Kode 6 digit dikirim ke <span class="font-semibold text-white">+62{{ session('otp_phone') }}</span>.
        </p>

        @if (session('otp_demo'))
            <div class="mt-4 flex items-center justify-between gap-3 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3">
                <span class="text-xs text-emerald-300">Demo OTP</span>
                <span class="font-mono text-lg font-extrabold tracking-[0.3em] text-emerald-300">{{ session('otp_demo') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login.otp.check') }}" class="mt-7 space-y-5">
            @csrf
            <div>
                <label for="code" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-400">Kode OTP</label>
                <input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus placeholder="123456"
                       class="w-full rounded-xl border border-white/10 bg-slate-900/70 px-4 py-3 text-center font-mono text-2xl tracking-[0.4em] text-white placeholder:text-slate-600 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                @error('code')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="w-full rounded-xl bg-emerald-500 py-3 text-sm font-bold text-white transition hover:bg-emerald-400">
                Verifikasi &amp; Masuk
            </button>
        </form>

        <a href="{{ route('login.otp') }}" class="mt-6 block text-center text-sm text-slate-400 hover:text-white">
            ← Ganti nomor / minta ulang
        </a>
    </div>
@endsection
