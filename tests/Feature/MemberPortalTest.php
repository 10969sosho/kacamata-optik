<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Frame;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\TransactionItem;
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

        $frame = Frame::create([
            'sku' => 'FR-001', 'name' => 'Ray-Ban Aviator', 'brand' => 'Ray-Ban',
            'sell_price' => 2500000, 'buy_price' => 1200000,
            'stock' => 5, 'min_stock' => 2, 'status' => 'active',
        ]);

        $this->transaction = Transaction::create([
            'sales_no' => 'TRX-20260930-00001',
            'sales_date' => now()->toDateString(),
            'sales_time' => '14:30:00',
            'customer_id' => $this->customer->id,
            'input_by' => 'Staff Optik',
            'payment_by' => 'cash',
            'grandtotal' => 2500000,
            'sales_status' => 'processing',
        ]);

        Payment::create([
            'sales_no' => $this->transaction->sales_no,
            'payment_method' => 'cash',
            'amount' => 2500000,
            'paid_at' => now(),
        ]);

        TransactionItem::create([
            'sales_no' => $this->transaction->sales_no,
            'sortno' => 1,
            'itemno' => (string) $frame->id,
            'item_type' => 'frame',
            'item_description' => 'Ray-Ban Aviator',
            'username' => 'Raka (Anak 1)',
            'qty' => 1,
            'unit' => 'pcs',
            'price' => 2500000,
            'disc_percent' => 0,
            'subtotal' => 2500000,
        ]);

        $this->transaction->prescriptions()->create([
            'sortno' => 1,
            'item_type' => 'frame',
            'qty' => 1,
            'sisi' => 'R',
            'sph' => '-2.50',
            'ro1' => 'Optometris Sari',
            'ro2' => 'Teknisi Rudi',
            'resep' => 'Optometris Dian',
        ]);

        $this->transaction->prescriptions()->create([
            'sortno' => 1,
            'item_type' => 'frame',
            'qty' => 1,
            'sisi' => 'L',
            'sph' => '-2.25',
            'ro1' => 'Optometris Sari',
            'ro2' => 'Teknisi Rudi',
            'resep' => 'Optometris Dian',
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
            ->assertSee($this->transaction->sales_no)
            ->assertSee('Lihat Detail')
            ->assertSee(route('portal.transactions.show', $this->transaction));
    }

    public function test_member_can_view_own_transaction_detail(): void
    {
        $this->actingAs($this->member)
            ->get(route('portal.transactions.show', $this->transaction))
            ->assertOk()
            ->assertSee($this->transaction->sales_no)
            ->assertSee('Ringkasan Pembayaran')
            ->assertSee('Pemakai: Raka (Anak 1)')
            ->assertSee('Diproses')
            ->assertSee('RO1 (Periksa Mata)')
            ->assertSee('Teknisi Rudi')
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
            'sales_no' => 'TRX-20260930-00002',
            'sales_date' => now()->toDateString(),
            'sales_time' => '15:00:00',
            'customer_id' => $otherCustomer->id,
            'input_by' => 'Staff Optik',
            'payment_by' => 'cash',
            'grandtotal' => 100000,
            'sales_status' => 'ordered',
        ]);

        $this->actingAs($this->member)
            ->get(route('portal.transactions.show', $otherTransaction))
            ->assertNotFound();
    }
}
