<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_guest_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_admin_can_login_with_email_and_is_redirected_to_dashboard(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@optik.com',
            'phone' => '081111111111',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->post(route('login'), ['login' => 'admin@optik.com', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_staff_can_login_with_phone(): void
    {
        User::create([
            'name' => 'Staff',
            'email' => 'staff@optik.com',
            'phone' => '081222222222',
            'password' => 'password',
            'role' => 'staff',
            'status' => 'active',
        ]);

        $this->post(route('login'), ['login' => '081222222222', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->post(route('login'), ['login' => 'nobody@optik.com', 'password' => 'salah'])
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_customer_cannot_use_staff_login(): void
    {
        User::create([
            'name' => 'Budi',
            'phone' => '081333333333',
            'password' => 'password',
            'role' => 'customer',
            'status' => 'active',
        ]);

        $this->post(route('login'), ['login' => '081333333333', 'password' => 'password'])
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_whatsapp_otp_flow_logs_customer_in_and_creates_account(): void
    {
        $this->post(route('login.otp.send'), ['phone' => '081999999999'])
            ->assertRedirect(route('login.otp.verify'));

        $otp = session('otp_demo');
        $this->assertMatchesRegularExpression('/^\d{6}$/', (string) $otp);
        $this->assertGuest();

        $this->post(route('login.otp.check'), ['code' => $otp])
            ->assertRedirect(route('portal.index'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['phone' => '081999999999', 'role' => 'customer']);
        $this->assertDatabaseHas('customers', ['phone' => '081999999999']);
    }

    public function test_existing_customer_logs_in_through_otp(): void
    {
        $user = User::create([
            'name' => 'Budi Santoso',
            'phone' => '081298765432',
            'password' => 'password',
            'role' => 'customer',
            'status' => 'active',
        ]);

        Customer::create([
            'user_id' => $user->id,
            'member_id' => 'KCM-000012',
            'name' => 'Budi Santoso',
            'phone' => '081298765432',
            'registered_at' => '2025-03-10',
            'status' => 'active',
        ]);

        $this->post(route('login.otp.send'), ['phone' => '081298765432']);
        $this->post(route('login.otp.check'), ['code' => session('otp_demo')])
            ->assertRedirect(route('portal.index'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, Customer::where('phone', '081298765432')->count());
    }

    public function test_wrong_otp_is_rejected(): void
    {
        $this->post(route('login.otp.send'), ['phone' => '081999999998']);

        $this->post(route('login.otp.check'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    public function test_otp_login_without_leading_zero_reuses_existing_member(): void
    {
        $user = User::create([
            'name' => 'Budi Santoso',
            'phone' => '081298765432',
            'password' => 'password',
            'role' => 'customer',
            'status' => 'active',
        ]);

        Customer::create([
            'user_id' => $user->id,
            'member_id' => 'KCM-000012',
            'name' => 'Budi Santoso',
            'phone' => '081298765432',
            'registered_at' => '2025-03-10',
            'status' => 'active',
        ]);

        $this->post(route('login.otp.send'), ['phone' => '81298765432']);
        $this->post(route('login.otp.check'), ['code' => session('otp_demo')])
            ->assertRedirect(route('portal.index'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, User::where('phone', '081298765432')->count());
        $this->assertSame(0, User::where('phone', '81298765432')->count());
        $this->assertSame(1, Customer::count());
    }

    public function test_otp_login_with_country_code_reuses_existing_member(): void
    {
        $user = User::create([
            'name' => 'Budi Santoso',
            'phone' => '081298765432',
            'password' => 'password',
            'role' => 'customer',
            'status' => 'active',
        ]);

        Customer::create([
            'user_id' => $user->id,
            'member_id' => 'KCM-000012',
            'name' => 'Budi Santoso',
            'phone' => '081298765432',
            'registered_at' => '2025-03-10',
            'status' => 'active',
        ]);

        $this->post(route('login.otp.send'), ['phone' => '6281298765432']);
        $this->post(route('login.otp.check'), ['code' => session('otp_demo')])
            ->assertRedirect(route('portal.index'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, Customer::count());
    }

    public function test_verify_page_redirects_when_no_otp_session(): void
    {
        $this->get(route('login.otp.verify'))->assertRedirect(route('login.otp'));
    }

    public function test_logout_invalidates_session(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@optik.com',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
