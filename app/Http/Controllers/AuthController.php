<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $field = filter_var($data['login'], FILTER_VALIDATE_EMAIL) !== false ? 'email' : 'phone';

        $user = User::query()
            ->where($field, $data['login'])
            ->where('status', 'active')
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => 'Kredensial tidak valid.',
            ]);
        }

        if ($user->isCustomer()) {
            throw ValidationException::withMessages([
                'login' => 'Akun customer silakan masuk lewat WhatsApp OTP.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function showOtpLogin(): View
    {
        return view('auth.otp-request');
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'min:8', 'max:20'],
        ]);

        $phone = preg_replace('/\D+/', '', $data['phone']) ?? '';

        if (strlen($phone) < 8) {
            throw ValidationException::withMessages(['phone' => 'Nomor WhatsApp tidak valid.']);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put("login_otp:{$phone}", $code, now()->addMinutes(5));
        $request->session()->put(['otp_phone' => $phone, 'otp_demo' => $code]);

        return redirect()->route('login.otp.verify');
    }

    public function showVerify(Request $request): RedirectResponse|View
    {
        if (! $request->session()->has('otp_phone')) {
            return redirect()->route('login.otp');
        }

        return view('auth.otp-verify');
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $phone = $request->session()->get('otp_phone');

        if (! $phone) {
            return redirect()->route('login.otp')->withErrors(['phone' => 'Sesi OTP berakhir, silakan minta ulang.']);
        }

        $stored = Cache::pull("login_otp:{$phone}");

        if (! is_string($stored) || ! hash_equals($stored, $data['code'])) {
            throw ValidationException::withMessages([
                'code' => 'Kode OTP salah atau sudah kedaluwarsa.',
            ]);
        }

        $user = $this->resolveCustomerUser($phone);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget(['otp_phone', 'otp_demo']);

        return redirect()->intended(route('portal.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function resolveCustomerUser(string $phone): User
    {
        $user = User::query()->where('phone', $phone)->where('role', 'customer')->first();

        if (! $user) {
            $user = User::create([
                'name' => 'Member '.$phone,
                'phone' => $phone,
                'password' => uniqid('otp-', true),
                'role' => 'customer',
                'status' => 'active',
            ]);
        }

        $customer = Customer::query()->where('phone', $phone)->first()
            ?? Customer::query()->where('user_id', $user->id)->first();

        if (! $customer) {
            $customer = Customer::create([
                'user_id' => $user->id,
                'member_id' => Customer::generateMemberId(),
                'name' => $user->name,
                'phone' => $phone,
                'registered_at' => now()->toDateString(),
                'status' => 'active',
            ]);
        } elseif (! $customer->user_id) {
            $customer->update(['user_id' => $user->id]);
        }

        return $user;
    }
}
