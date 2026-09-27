@extends('layouts.auth')

@section('title', 'Verifikasi OTP')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <h2 class="text-2xl font-extrabold text-slate-900">Verifikasi Kode</h2>
        <p class="mt-1 text-sm text-slate-500">
            Kode 6 digit dikirim ke <span class="font-semibold text-slate-900">+62{{ session('otp_phone') }}</span>.
        </p>

        @if (session('otp_demo'))
            <div class="mt-4 flex items-center justify-between gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3">
                <span class="text-xs text-red-600">Demo OTP</span>
                <span class="font-mono text-lg font-extrabold tracking-[0.3em] text-red-600">{{ session('otp_demo') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login.otp.check') }}" class="mt-7 space-y-5">
            @csrf
            <div>
                <label for="code" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Kode OTP</label>
                <input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus placeholder="123456"
                       class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-center font-mono text-2xl text-slate-900 tracking-[0.4em] text-slate-900 placeholder:text-slate-500 focus:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-600/25">
                @error('code')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="w-full rounded-xl bg-red-600 py-3 text-sm font-bold text-white transition hover:bg-red-700">
                Verifikasi &amp; Masuk
            </button>
        </form>

        <a href="{{ route('login.otp') }}" class="mt-6 block text-center text-sm text-slate-500 hover:text-red-600">
            ← Ganti nomor / minta ulang
        </a>
    </div>
@endsection
