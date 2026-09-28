@extends('layouts.auth')

@section('title', 'Login WhatsApp')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <h2 class="text-2xl font-extrabold text-slate-900">Login Customer</h2>
        <p class="mt-1 text-sm text-slate-500">Masukkan nomor WhatsApp untuk menerima kode OTP.</p>

        <form method="POST" action="{{ route('login.otp.send') }}" class="mt-7 space-y-5">
            @csrf
            <div>
                <label for="phone" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Nomor WhatsApp</label>
                <div class="flex items-center rounded-xl border border-slate-200 bg-white focus-within:border-red-600 focus-within:ring-2 focus-within:ring-red-600/25">
                    <span class="pl-4 text-sm text-slate-500">+62</span>
                    <input id="phone" name="phone" type="tel" required value="{{ old('phone') }}" placeholder="81234567890"
                           class="w-full bg-transparent px-3 py-3 text-sm text-slate-900 placeholder:text-slate-500 focus:outline-none">
                </div>
                <p class="mt-1.5 text-xs text-slate-400">Boleh diawali 0, +62, atau 62 — semuanya dianggap nomor yang sama.</p>
                @error('phone')<p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="w-full rounded-xl bg-red-600 py-3 text-sm font-bold text-white transition hover:bg-red-700">
                Kirim Kode OTP
            </button>
        </form>

        <a href="{{ route('login') }}" class="mt-6 block text-center text-sm text-slate-500 hover:text-red-600">
            ← Kembali ke login admin / staff
        </a>
    </div>
@endsection
