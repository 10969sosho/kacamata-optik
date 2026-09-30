<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Frame;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\TransactionUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class MemberPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $member;

    private Customer $customer;

    private Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->member = User::create([
            'name' => 'Budi', 'phone' => '081298765432', 'password' => 'password',
            'role' => 'customer', 'status' => 'active',
        ]);

        $this->customer = Customer::create([
            'user_id' => $this->member->id, 'member_id' => 'KCM-000012', 'name' => 'Budi Santoso',
            'phone' => '081298765432', 'status' => 'active', 'registered_at' => now()->toDateString(),
        ]);

        $store = Store::create([
            'code' => 'OPT-PST', 'name' => 'Kacamata Optik Pusat', 'phone' => '0215550123',
            'address' => 'Jl. MH Thamrin No. 10, Jakarta Pusat', 'is_active' => true,
        ]);

        $frame = Frame::create([
            'sku' => 'FR-001', 'name' => 'Ray-Ban Aviator', 'brand' => 'Ray-Ban',
            'sell_price' => 2500000, 'buy_price' => 1200000,
            'stock' => 5, 'min_stock' => 2, 'status' => 'active',
        ]);

        $rx = Prescription::create([
            'customer_id' => $this->customer->id,
            'doctor_or_optician' => 'Optometris Dian',
            'examination_date' => now()->toDateString(),
            'od_sph' => '-2.50', 'os_sph' => '-2.25', 'pd_total' => '60',
        ]);

        $this->transaction = Transaction::create([
            'invoice_number' => 'TRX-20260930-00001',
            'store_id' => $store->id,
            'customer_id' => $this->customer->id,
            'prescription_id' => $rx->id,
            'subtotal' => 2500000,
            'discount_amount' => 0,
            'total_amount' => 2500000,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'status' => 'completed',
            'transaction_date' => now(),
        ]);

        Payment::create([
            'transaction_id' => $this->transaction->id,
            'payment_method' => 'cash',
            'amount' => 2500000,
            'paid_at' => now(),
        ]);

        $wearer = TransactionUser::create([
            'transaction_id' => $this->transaction->id,
            'name' => 'Raka (Anak 1)',
            'prescription_id' => $rx->id,
        ]);

        TransactionItem::create([
            'transaction_id' => $this->transaction->id,
            'transaction_user_id' => $wearer->id,
            'item_type' => 'frame',
            'frame_id' => $frame->id,
            'name' => 'Ray-Ban Aviator',
            'quantity' => 1,
            'price' => 2500000,
            'discount' => 0,
            'subtotal' => 2500000,
        ]);
    }

    public function test_resep_menu_is_removed_from_member_portal(): void
    {
        $this->assertFalse(Route::has('portal.prescriptions'));

        $this->actingAs($this->member)
            ->get(route('portal.index'))
            ->assertOk()
            ->assertDontSee('Resep Aktif')
            ->assertDontSee('file-heart')
            ->assertSee('Status Pesanan Terkini');

        $this->actingAs($this->member)->get('/portal/prescriptions')->assertNotFound();
    }

    public function test_transaction_history_shows_detail_link(): void
    {
        $this->actingAs($this->member)
            ->get(route('portal.transactions'))
            ->assertOk()
            ->assertSee($this->transaction->invoice_number)
            ->assertSee('Lihat Detail')
            ->assertSee(route('portal.transactions.show', $this->transaction));
    }

    public function test_member_can_view_own_transaction_detail(): void
    {
        $this->actingAs($this->member)
            ->get(route('portal.transactions.show', $this->transaction))
            ->assertOk()
            ->assertSee($this->transaction->invoice_number)
            ->assertSee('Ringkasan Pembayaran')
            ->assertSee('Pemakai: Raka (Anak 1)')
            ->assertSee('Ray-Ban Aviator')
            ->assertSee('Resep Kacamata')
            ->assertSee('Riwayat Pembayaran')
            ->assertSee(route('portal.transactions'));
    }

    public function test_member_cannot_view_other_member_transaction(): void
    {
        $otherUser = User::create([
            'name' => 'Siti', 'phone' => '081111111111', 'password' => 'password',
            'role' => 'customer', 'status' => 'active',
        ]);

        $otherCustomer = Customer::create([
            'user_id' => $otherUser->id, 'member_id' => 'KCM-000099', 'name' => 'Siti',
            'phone' => '081111111111', 'status' => 'active', 'registered_at' => now()->toDateString(),
        ]);

        $otherTransaction = Transaction::create([
            'invoice_number' => 'TRX-20260930-00002',
            'customer_id' => $otherCustomer->id,
            'subtotal' => 100000,
            'discount_amount' => 0,
            'total_amount' => 100000,
            'payment_status' => 'unpaid',
            'payment_method' => 'cash',
            'status' => 'ordered',
            'transaction_date' => now(),
        ]);

        $this->actingAs($this->member)
            ->get(route('portal.transactions.show', $otherTransaction))
            ->assertNotFound();
    }
}
