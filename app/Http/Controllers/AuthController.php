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

        $isEmail = filter_var($data['login'], FILTER_VALIDATE_EMAIL) !== false;

        $user = User::query()
            ->when(
                $isEmail,
                fn ($query) => $query->where('email', $data['login']),
                fn ($query) => $query->whereIn('phone', $this->phoneVariants($data['login'])),
            )
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

        $phone = $this->normalizePhone($data['phone']);

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
        $phone = $this->normalizePhone($phone);
        $variants = $this->phoneVariants($phone);

        $customer = Customer::query()->whereIn('phone', $variants)->first();

        if ($customer?->user_id) {
            $linked = User::query()->find($customer->user_id);

            if ($linked) {
                return $linked;
            }
        }

        $user = User::query()
            ->whereIn('phone', $variants)
            ->where('role', 'customer')
            ->first();

        if (! $user) {
            $user = User::create([
                'name' => 'Member '.$phone,
                'phone' => $phone,
                'password' => uniqid('otp-', true),
                'role' => 'customer',
                'status' => 'active',
            ]);
        }

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

    /**
     * Normalise a WhatsApp number to the local 08xx format used throughout the database.
     *
     * The OTP form asks for 81234567890 (the +62 prefix is rendered next to the input)
     * while customers are stored as 081234567890, so both, plus 6281234567890, must
     * resolve to the same member record instead of spawning an empty duplicate account.
     */
    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '62') && strlen($digits) > 10) {
            $digits = '0'.substr($digits, 2);
        }

        if (str_starts_with($digits, '8')) {
            $digits = '0'.$digits;
        }

        return $digits;
    }

    /**
     * Every written form of a number so rows saved before normalisation still match.
     *
     * @return list<string>
     */
    private function phoneVariants(string $phone): array
    {
        $local = $this->normalizePhone($phone);

        if ($local === '') {
            return [trim($phone)];
        }

        return array_values(array_unique([
            $local,
            ltrim($local, '0'),
            '62'.substr($local, 1),
        ]));
    }
}
