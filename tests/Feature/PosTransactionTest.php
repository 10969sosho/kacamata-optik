<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Frame;
use App\Models\Lens;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosTransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private Frame $frame;

    private Lens $lens;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::create([
            'name' => 'Staff', 'email' => 'staff@optik.com', 'password' => 'password',
            'role' => 'staff', 'status' => 'active',
        ]);

        $this->frame = Frame::create([
            'sku' => 'FR-001', 'name' => 'Ray-Ban Aviator', 'brand' => 'Ray-Ban',
            'sell_price' => 2500000, 'buy_price' => 1200000,
            'stock' => 5, 'min_stock' => 2, 'status' => 'active',
        ]);

        $this->lens = Lens::create([
            'sku' => 'LS-001', 'brand' => 'Essilor', 'name' => 'Crizal Easy 1.56',
            'lens_type' => 'Single Vision', 'index_val' => '1.56',
            'sell_price' => 1500000, 'buy_price' => 700000,
            'stock' => 10, 'min_stock' => 3,
        ]);

        $this->customer = Customer::create([
            'member_id' => 'KCM-000012', 'name' => 'Budi Santoso', 'phone' => '081298765432',
            'status' => 'active', 'registered_at' => now()->toDateString(),
        ]);
    }

    public function test_pos_page_renders(): void
    {
        $this->actingAs($this->staff)->get(route('pos.create'))->assertOk()->assertSee('Keranjang');
    }

    public function test_pos_creates_transaction_items_payment_and_decrements_stock(): void
    {
        $this->actingAs($this->staff)->post(route('pos.store'), [
            'customer_id' => $this->customer->id,
            'items' => [
                ['type' => 'frame', 'id' => $this->frame->id, 'qty' => 1],
                ['type' => 'lens', 'id' => $this->lens->id, 'qty' => 2],
            ],
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'processing',
        ])->assertRedirect();

        $transaction = Transaction::latest('id')->firstOrFail();

        $this->assertSame(5500000.0, (float) $transaction->subtotal);
        $this->assertSame(0.0, (float) $transaction->discount_amount);
        $this->assertSame(5500000.0, (float) $transaction->total_amount);
        $this->assertSame('paid', $transaction->payment_status);
        $this->assertSame('processing', $transaction->status);
        $this->assertMatchesRegularExpression('/^TRX-\d{8}-\d{5}$/', $transaction->invoice_number);

        $this->assertCount(2, $transaction->items);
        $this->assertSame(1, Payment::where('transaction_id', $transaction->id)->count());
        $this->assertSame(5500000.0, (float) Payment::where('transaction_id', $transaction->id)->value('amount'));

        $this->assertSame(4, $this->frame->fresh()->stock);
        $this->assertSame(8, $this->lens->fresh()->stock);
    }

    public function test_pos_applies_percentage_promo_discount(): void
    {
        $promo = Promotion::create([
            'name' => 'Diskon 10%', 'promo_type' => 'percentage', 'discount_value' => 10,
            'min_spend' => 1000000, 'is_active' => true,
            'start_date' => now()->subDay(), 'end_date' => now()->addMonth(),
        ]);

        $this->actingAs($this->staff)->post(route('pos.store'), [
            'customer_id' => $this->customer->id,
            'items' => [['type' => 'frame', 'id' => $this->frame->id, 'qty' => 1]],
            'promo_id' => $promo->id,
            'payment_method' => 'qris',
            'payment_status' => 'paid',
            'status' => 'ordered',
        ])->assertRedirect();

        $transaction = Transaction::latest('id')->firstOrFail();

        $this->assertSame(2500000.0, (float) $transaction->subtotal);
        $this->assertSame(250000.0, (float) $transaction->discount_amount);
        $this->assertSame(2250000.0, (float) $transaction->total_amount);
        $this->assertSame($promo->id, $transaction->promo_id);
    }

    public function test_pos_down_payment_requires_amount_and_records_partial_payment(): void
    {
        $this->actingAs($this->staff)->post(route('pos.store'), [
            'customer_id' => $this->customer->id,
            'items' => [['type' => 'frame', 'id' => $this->frame->id, 'qty' => 1]],
            'payment_method' => 'transfer',
            'payment_status' => 'down_payment',
            'status' => 'ordered',
        ])->assertSessionHasErrors('dp_amount');

        $this->actingAs($this->staff)->post(route('pos.store'), [
            'customer_id' => $this->customer->id,
            'items' => [['type' => 'frame', 'id' => $this->frame->id, 'qty' => 1]],
            'payment_method' => 'transfer',
            'payment_status' => 'down_payment',
            'dp_amount' => 1000000,
            'status' => 'ordered',
        ])->assertRedirect();

        $transaction = Transaction::latest('id')->firstOrFail();

        $this->assertSame('down_payment', $transaction->payment_status);
        $this->assertSame(1000000.0, $transaction->amountPaid());
        $this->assertSame(1500000.0, $transaction->balanceDue());
    }

    public function test_pos_rejects_empty_cart_and_insufficient_stock(): void
    {
        $this->actingAs($this->staff)->post(route('pos.store'), [
            'customer_id' => $this->customer->id,
            'items' => [],
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'ordered',
        ])->assertSessionHasErrors('items');

        $this->actingAs($this->staff)->post(route('pos.store'), [
            'customer_id' => $this->customer->id,
            'items' => [['type' => 'frame', 'id' => $this->frame->id, 'qty' => 99]],
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'ordered',
        ])->assertSessionHasErrors('items');

        $this->assertSame(5, $this->frame->fresh()->stock);
        $this->assertSame(0, Transaction::count());
    }

    public function test_pos_can_create_inline_prescription(): void
    {
        $this->actingAs($this->staff)->post(route('pos.store'), [
            'customer_id' => $this->customer->id,
            'items' => [['type' => 'frame', 'id' => $this->frame->id, 'qty' => 1]],
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'ordered',
            'new_prescription' => 1,
            'prescription' => [
                'doctor_or_optician' => 'Dr. Robert Sp.M',
                'examination_date' => now()->toDateString(),
                'od_sph' => '-1.25',
                'os_sph' => '-1.00',
            ],
        ])->assertRedirect();

        $transaction = Transaction::latest('id')->firstOrFail();

        $this->assertNotNull($transaction->prescription_id);
        $this->assertSame($this->customer->id, $transaction->prescription->customer_id);
        $this->assertSame('Dr. Robert Sp.M', $transaction->prescription->doctor_or_optician);
    }

    public function test_pos_quick_customer_endpoint(): void
    {
        $response = $this->actingAs($this->staff)->postJson(route('pos.customers'), [
            'name' => 'Pelanggan Baru', 'phone' => '081777777777',
        ]);

        $response->assertCreated()->assertJsonStructure(['id', 'name', 'member_id']);
        $this->assertDatabaseHas('customers', ['phone' => '081777777777']);
    }

    public function test_transaction_status_can_be_updated(): void
    {
        $transaction = Transaction::create([
            'invoice_number' => 'TRX-20260926-00001',
            'customer_id' => $this->customer->id,
            'staff_id' => $this->staff->id,
            'subtotal' => 100000, 'discount_amount' => 0, 'total_amount' => 100000,
            'payment_status' => 'paid', 'payment_method' => 'cash',
            'status' => 'ordered', 'transaction_date' => now(),
        ]);

        $this->actingAs($this->staff)
            ->patch(route('transactions.status', $transaction), ['status' => 'ready'])
            ->assertSessionHas('success');

        $this->assertSame('ready', $transaction->fresh()->status);
    }

    public function test_transaction_detail_and_print_pages_render(): void
    {
        $this->actingAs($this->staff)->post(route('pos.store'), [
            'customer_id' => $this->customer->id,
            'items' => [['type' => 'frame', 'id' => $this->frame->id, 'qty' => 1]],
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'ordered',
        ]);

        $transaction = Transaction::latest('id')->firstOrFail();

        $this->actingAs($this->staff)->get(route('transactions.show', $transaction))
            ->assertOk()
            ->assertSee($transaction->invoice_number);

        $this->actingAs($this->staff)->get(route('transactions.print', $transaction))
            ->assertOk()
            ->assertSee('TOTAL');
    }
}
