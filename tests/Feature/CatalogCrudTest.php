<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Frame;
use App\Models\Lens;
use App\Models\Prescription;
use App\Models\ProductCategory;
use App\Models\Promotion;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@optik.com', 'password' => 'password',
            'role' => 'admin', 'status' => 'active',
        ]);
    }

    public function test_dashboard_renders_with_kpis(): void
    {
        $this->actingAs($this->admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Sales Hari Ini')
            ->assertSee('Low Stock Alert');
    }

    public function test_frame_crud_flow(): void
    {
        $this->actingAs($this->admin)->post(route('frames.store'), [
            'sku' => 'FR-NEW', 'name' => 'Oakley Holbrook', 'brand' => 'Oakley',
            'size' => '55-18-137', 'buy_price' => 950000, 'sell_price' => 1950000,
            'stock' => 10, 'min_stock' => 3, 'status' => 'active',
        ])->assertRedirect(route('frames.index'));

        $frame = Frame::where('sku', 'FR-NEW')->firstOrFail();
        $this->assertDatabaseHas('frames', ['sku' => 'FR-NEW', 'brand' => 'Oakley']);

        $this->actingAs($this->admin)->put(route('frames.update', $frame), [
            'sku' => 'FR-NEW', 'name' => 'Oakley Holbrook XL', 'brand' => 'Oakley',
            'buy_price' => 950000, 'sell_price' => 2100000,
            'stock' => 8, 'min_stock' => 3, 'status' => 'active',
        ])->assertRedirect(route('frames.index'));

        $this->assertSame('Oakley Holbrook XL', $frame->fresh()->name);

        $this->actingAs($this->admin)->delete(route('frames.destroy', $frame))
            ->assertRedirect(route('frames.index'));

        $this->assertDatabaseMissing('frames', ['id' => $frame->id]);
    }

    public function test_frame_index_filters_by_search(): void
    {
        Frame::create(['sku' => 'FR-A', 'name' => 'Ray-Ban Wayfarer', 'brand' => 'Ray-Ban', 'sell_price' => 1, 'buy_price' => 1, 'stock' => 1, 'min_stock' => 1, 'status' => 'active']);
        Frame::create(['sku' => 'FR-B', 'name' => 'Gucci Square', 'brand' => 'Gucci', 'sell_price' => 1, 'buy_price' => 1, 'stock' => 1, 'min_stock' => 1, 'status' => 'active']);

        $this->actingAs($this->admin)->get(route('frames.index', ['q' => 'gucci']))
            ->assertOk()
            ->assertSee('Gucci Square')
            ->assertDontSee('Ray-Ban Wayfarer');
    }

    public function test_lens_crud_flow(): void
    {
        $this->actingAs($this->admin)->post(route('lenses.store'), [
            'sku' => 'LS-NEW', 'brand' => 'Zeiss', 'name' => 'DriveSafe 1.67',
            'lens_type' => 'Progressive', 'index_val' => '1.67',
            'buy_price' => 2500000, 'sell_price' => 5200000,
            'stock' => 6, 'min_stock' => 5,
        ])->assertRedirect(route('lenses.index'));

        $lens = Lens::where('sku', 'LS-NEW')->firstOrFail();

        $this->actingAs($this->admin)->put(route('lenses.update', $lens), [
            'sku' => 'LS-NEW', 'brand' => 'Zeiss', 'name' => 'DriveSafe Plus 1.67',
            'lens_type' => 'Progressive', 'index_val' => '1.67',
            'buy_price' => 2500000, 'sell_price' => 5400000,
            'stock' => 5, 'min_stock' => 5,
        ])->assertRedirect(route('lenses.index'));

        $this->assertSame('DriveSafe Plus 1.67', $lens->fresh()->name);

        $this->actingAs($this->admin)->delete(route('lenses.destroy', $lens))
            ->assertRedirect(route('lenses.index'));

        $this->assertDatabaseMissing('lenses', ['id' => $lens->id]);
    }

    public function test_category_crud_flow(): void
    {
        $this->actingAs($this->admin)->post(route('categories.store'), [
            'name' => 'Lensa Progresif Premium', 'type' => 'lens', 'is_active' => '1',
        ])->assertRedirect(route('categories.index'));

        $category = ProductCategory::where('name', 'Lensa Progresif Premium')->firstOrFail();
        $this->assertNotEmpty($category->slug);

        $this->actingAs($this->admin)->put(route('categories.update', $category), [
            'name' => 'Lensa Progresif Premium', 'type' => 'lens', 'is_active' => '1',
        ])->assertRedirect(route('categories.index'));

        $this->actingAs($this->admin)->delete(route('categories.destroy', $category))
            ->assertRedirect(route('categories.index'));

        $this->assertDatabaseMissing('product_categories', ['id' => $category->id]);
    }

    public function test_customer_crud_generates_member_id_and_shows_history(): void
    {
        $this->actingAs($this->admin)->post(route('customers.store'), [
            'name' => 'Maya Anggraini', 'phone' => '081555555555',
            'email' => 'maya@test.com', 'gender' => 'Perempuan',
        ])->assertRedirect();

        $customer = Customer::where('phone', '081555555555')->firstOrFail();
        $this->assertMatchesRegularExpression('/^KCM-\d{6}$/', $customer->member_id);

        $this->actingAs($this->admin)->get(route('customers.show', $customer))
            ->assertOk()
            ->assertSee($customer->member_id)
            ->assertSee('Maya Anggraini');

        $this->actingAs($this->admin)->put(route('customers.update', $customer), [
            'name' => 'Maya A.', 'phone' => '081555555555',
        ])->assertRedirect(route('customers.show', $customer));

        $this->assertSame('Maya A.', $customer->fresh()->name);
    }

    public function test_prescription_create_and_show(): void
    {
        $customer = Customer::create([
            'member_id' => 'KCM-000020', 'name' => 'Siti', 'phone' => '081666666666',
            'status' => 'active', 'registered_at' => now()->toDateString(),
        ]);

        $this->actingAs($this->admin)->post(route('prescriptions.store'), [
            'customer_id' => $customer->id,
            'doctor_or_optician' => 'Optometris Dian',
            'examination_date' => now()->toDateString(),
            'prescription_type' => 'Distance',
            'source' => 'bring_own',
            'od_sph' => '-1.25', 'od_cyl' => '-0.50', 'od_axis' => '90',
            'os_sph' => '-1.00', 'pd_total' => '63',
        ])->assertRedirect();

        $rx = Prescription::firstOrFail();

        $this->assertSame('bring_own', $rx->source);
        $this->assertSame('Bawa Resep Sendiri', $rx->sourceLabel());

        $this->actingAs($this->admin)->get(route('prescriptions.show', $rx))
            ->assertOk()
            ->assertSee('Optometris Dian')
            ->assertSee('Bawa Resep Sendiri')
            ->assertSee('-1.25');
    }

    public function test_prescription_source_defaults_to_in_store(): void
    {
        $customer = Customer::create([
            'member_id' => 'KCM-000021', 'name' => 'Rudi', 'phone' => '081666666667',
            'status' => 'active', 'registered_at' => now()->toDateString(),
        ]);

        $this->actingAs($this->admin)->post(route('prescriptions.store'), [
            'customer_id' => $customer->id,
            'doctor_or_optician' => 'Dr. Robert Sp.M',
            'examination_date' => now()->toDateString(),
        ])->assertRedirect();

        $rx = Prescription::firstOrFail();

        $this->assertSame('in_store', $rx->source);
        $this->assertSame('In Store', $rx->sourceLabel());

        $this->actingAs($this->admin)->post(route('prescriptions.store'), [
            'customer_id' => $customer->id,
            'doctor_or_optician' => 'Dr. Robert Sp.M',
            'examination_date' => now()->toDateString(),
            'source' => 'di_kantor',
        ])->assertSessionHasErrors('source');
    }

    public function test_promotion_crud_flow(): void
    {
        $this->actingAs($this->admin)->post(route('promotions.store'), [
            'name' => 'Cashback 100rb', 'promo_type' => 'nominal', 'discount_value' => 100000,
            'min_spend' => 1000000, 'is_active' => '1',
            'start_date' => now()->toDateString(), 'end_date' => now()->addMonth()->toDateString(),
        ])->assertRedirect(route('promotions.index'));

        $promo = Promotion::where('name', 'Cashback 100rb')->firstOrFail();

        $this->actingAs($this->admin)->put(route('promotions.update', $promo), [
            'name' => 'Cashback 150rb', 'promo_type' => 'nominal', 'discount_value' => 150000,
            'min_spend' => 1000000, 'is_active' => '1',
        ])->assertRedirect(route('promotions.index'));

        $this->assertSame('Cashback 150rb', $promo->fresh()->name);

        $this->actingAs($this->admin)->delete(route('promotions.destroy', $promo))
            ->assertRedirect(route('promotions.index'));

        $this->assertDatabaseMissing('promotions', ['id' => $promo->id]);
    }

    public function test_report_page_and_csv_export(): void
    {
        $customer = Customer::create([
            'member_id' => 'KCM-000030', 'name' => 'Hendra', 'phone' => '081444444444',
            'status' => 'active', 'registered_at' => now()->toDateString(),
        ]);

        Transaction::create([
            'invoice_number' => 'TRX-20260926-00002', 'customer_id' => $customer->id,
            'staff_id' => $this->admin->id,
            'subtotal' => 2000000, 'discount_amount' => 200000, 'total_amount' => 1800000,
            'payment_status' => 'paid', 'payment_method' => 'cash',
            'status' => 'completed', 'transaction_date' => now(),
        ]);

        $this->actingAs($this->admin)->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Total Gross Sales')
            ->assertSee('Total Net Sales')
            ->assertSee('Download CSV');

        $csv = $this->actingAs($this->admin)->get(route('reports.export'));
        $csv->assertOk();
        $this->assertStringContainsString('text/csv', (string) $csv->headers->get('Content-Type'));
        $this->assertStringContainsString('TRX-20260926-00002', $csv->streamedContent());
    }

    public function test_pagination_and_listing_pages_render(): void
    {
        foreach (['frames.index', 'lenses.index', 'categories.index', 'customers.index', 'prescriptions.index', 'transactions.index', 'promotions.index', 'reports.index'] as $route) {
            $this->actingAs($this->admin)->get(route($route))->assertOk();
        }
    }
}
