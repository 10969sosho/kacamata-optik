@extends('layouts.auth')

@section('title', 'Login WhatsApp')

@section('content')
    <div class="rounded-2xl border border-white/10 bg-white/5 p-8 backdrop-blur">
        <h2 class="text-2xl font-extrabold text-white">Login Customer</h2>
        <p class="mt-1 text-sm text-slate-400">Masukkan nomor WhatsApp untuk menerima kode OTP.</p>

        <form method="POST" action="{{ route('login.otp.send') }}" class="mt-7 space-y-5">
            @csrf
            <div>
                <label for="phone" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-400">Nomor WhatsApp</label>
                <div class="flex items-center rounded-xl border border-white/10 bg-slate-900/70 focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-500/30">
                    <span class="pl-4 text-sm text-slate-400">+62</span>
                    <input id="phone" name="phone" type="tel" required value="{{ old('phone') }}" placeholder="81234567890"
                           class="w-full bg-transparent px-3 py-3 text-sm text-white placeholder:text-slate-500 focus:outline-none">
                </div>
                @error('phone')<p class="mt-1.5 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="w-full rounded-xl bg-emerald-500 py-3 text-sm font-bold text-white transition hover:bg-emerald-400">
                Kirim Kode OTP
            </button>
        </form>

        <a href="{{ route('login') }}" class="mt-6 block text-center text-sm text-slate-400 hover:text-white">
            ← Kembali ke login admin / staff
        </a>
    </div>
@endsection
