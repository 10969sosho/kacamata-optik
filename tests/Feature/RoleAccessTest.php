<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'admin@optik.com', 'password' => 'password',
            'role' => 'admin', 'status' => 'active',
        ]);
    }

    private function staff(): User
    {
        return User::create([
            'name' => 'Staff', 'email' => 'staff@optik.com', 'password' => 'password',
            'role' => 'staff', 'status' => 'active',
        ]);
    }

    private function customer(): User
    {
        $user = User::create([
            'name' => 'Budi', 'phone' => '081298765432', 'password' => 'password',
            'role' => 'customer', 'status' => 'active',
        ]);

        Customer::create([
            'user_id' => $user->id, 'member_id' => 'KCM-000012', 'name' => 'Budi',
            'phone' => '081298765432', 'status' => 'active', 'registered_at' => now()->toDateString(),
        ]);

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('pos.create'))->assertRedirect(route('login'));
        $this->get(route('portal.index'))->assertRedirect(route('login'));
    }

    public function test_admin_can_access_every_page(): void
    {
        $admin = $this->admin();

        foreach ([
            'dashboard', 'frames.index', 'frames.create', 'lenses.index', 'lenses.create',
            'categories.index', 'customers.index', 'customers.create', 'prescriptions.index',
            'prescriptions.create', 'pos.create', 'transactions.index', 'promotions.index',
            'promotions.create', 'reports.index',
        ] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }
    }

    public function test_staff_can_access_pos_customer_and_transactions(): void
    {
        $staff = $this->staff();

        foreach (['dashboard', 'pos.create', 'customers.index', 'transactions.index', 'prescriptions.index'] as $route) {
            $this->actingAs($staff)->get(route($route))->assertOk();
        }
    }

    public function test_staff_cannot_access_master_products_reports_or_promos(): void
    {
        $staff = $this->staff();

        $this->actingAs($staff)->get(route('frames.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('lenses.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('categories.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('promotions.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('reports.index'))->assertForbidden();
    }

    public function test_customer_cannot_access_erp_pages(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)->get(route('dashboard'))->assertForbidden();
        $this->actingAs($customer)->get(route('pos.create'))->assertForbidden();
        $this->actingAs($customer)->get(route('frames.index'))->assertForbidden();
    }

    public function test_customer_can_access_portal_only(): void
    {
        $customer = $this->customer();

        foreach (['portal.index', 'portal.prescriptions', 'portal.transactions', 'portal.promos'] as $route) {
            $this->actingAs($customer)->get(route($route))->assertOk();
        }

        $this->actingAs($customer)->get(route('home'))->assertRedirect(route('portal.index'));
    }

    public function test_admin_home_redirects_to_dashboard(): void
    {
        $this->actingAs($this->admin())->get(route('home'))->assertRedirect(route('dashboard'));
    }
}
