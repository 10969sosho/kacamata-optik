<?php

namespace Tests\Feature;

use App\Models\Accessory;
use App\Models\Customer;
use App\Models\Frame;
use App\Models\Lens;
use App\Models\Payment;
use App\Models\ProductCategory;
use App\Models\Promotion;
use App\Models\Transaction;
use App\Models\TransactionItem;
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
        $this->seedSavedPrescription('Dr. Robert Sp.M');

        $this->actingAs($this->staff)->get(route('pos.create'))
            ->assertOk()
            ->assertSee('Keranjang')
            ->assertSee('Pemakai')
            ->assertSee('Tambah Pemakai')
            // tiap item pemakai harus ikut terkirim sebagai field form
            ->assertSee('users[${ui}][items][${liIdx}][type]', false)
            ->assertSee('users[${ui}][items][${liIdx}][id]', false)
            ->assertSee('users[${ui}][items][${liIdx}][qty]', false)
            // RO1 / RO2 dan resep (ref / input baru) juga harus ikut terkirim
            ->assertSee('users[${ui}][ro1]', false)
            ->assertSee('users[${ui}][ro2]', false)
            ->assertSee('users[${ui}][rx_ref]', false)
            // pop-up resep lensa: dua opsi, sumber resep, dan detail per sisi
            ->assertSee('Resep Lensa')
            ->assertSee('Input Resep Baru')
            ->assertSee('Pakai Resep Tersimpan')
            ->assertSee('Sumber Resep')
            ->assertSee('In Store')
            ->assertSee('Bawa Resep Sendiri')
            ->assertSee('Mata Kanan (R)')
            ->assertSee('Mata Kiri (L)')
            ->assertSee('Simpan Resep')
            ->assertSee('users[${ui}][prescription][${f}]', false)
            // daftar resep tersimpan ikut dirender di DOM lengkap dengan detail R/L
            ->assertSee('"lens_id"', false)
            ->assertSee('"sph"', false)
            ->assertSee('"bring_own"', false);
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

        $transaction = Transaction::firstOrFail();

        $this->assertSame(5500000.0, (float) $transaction->grandtotal);
        $this->assertSame('cash', $transaction->payment_by);
        $this->assertSame('processing', $transaction->sales_status);
        $this->assertSame($this->customer->id, $transaction->customer_id);
        $this->assertSame($this->staff->name, $transaction->input_by);
        $this->assertMatchesRegularExpression('/^TRX-\d{8}-\d{5}$/', $transaction->sales_no);

        $this->assertCount(2, $transaction->items);
        $this->assertSame(1, Payment::where('sales_no', $transaction->sales_no)->count());
        $this->assertSame(5500000.0, (float) Payment::where('sales_no', $transaction->sales_no)->value('amount'));
        $this->assertTrue($transaction->isPaid());

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

        $transaction = Transaction::firstOrFail();

        $this->assertSame(2250000.0, (float) $transaction->grandtotal);
        $this->assertSame(10.0, (float) $transaction->items->first()->disc_percent);
        $this->assertSame(2250000.0, (float) $transaction->items->first()->subtotal);
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

        $transaction = Transaction::firstOrFail();

        $this->assertSame(1000000.0, $transaction->amountPaid());
        $this->assertSame(1500000.0, $transaction->balanceDue());
        $this->assertFalse($transaction->isPaid());
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
            'items' => [['type' => 'lens', 'id' => $this->lens->id, 'qty' => 1]],
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'ordered',
            'new_prescription' => 1,
            'prescription' => [
                'doctor_or_optician' => 'Dr. Robert Sp.M',
                'prescription_type' => 'Distance',
                'od_sph' => '-1.25',
                'os_sph' => '-1.00',
            ],
        ])->assertRedirect();

        $transaction = Transaction::firstOrFail();

        $this->assertSame(2, $transaction->prescriptions()->count());
        $this->assertSame('Dr. Robert Sp.M', $transaction->prescriptions()->first()->resep);

        $right = $transaction->prescriptions()->where('sisi', 'R')->firstOrFail();
        $left = $transaction->prescriptions()->where('sisi', 'L')->firstOrFail();
        $this->assertSame('-1.25', $right->sph);
        $this->assertSame('-1.00', $left->sph);
        $this->assertSame($transaction->items->first()->sortno, $right->sortno);
        $this->assertSame('lens', $right->item_type);
    }

    public function test_pos_prescription_modal_saves_source_and_per_eye_lens_items(): void
    {
        $secondLens = Lens::create([
            'sku' => 'LS-002', 'brand' => 'Hoya', 'name' => 'Hoya 1.60',
            'lens_type' => 'Progressive', 'index_val' => '1.60',
            'sell_price' => 2000000, 'buy_price' => 900000,
            'stock' => 6, 'min_stock' => 2,
        ]);

        $this->actingAs($this->staff)->post(route('pos.store'), [
            'customer_id' => $this->customer->id,
            'users' => [
                [
                    'name' => 'Adi',
                    'items' => [
                        ['type' => 'frame', 'id' => $this->frame->id, 'qty' => 1],
                        ['type' => 'lens', 'id' => $this->lens->id, 'qty' => 1],
                        ['type' => 'lens', 'id' => $secondLens->id, 'qty' => 1],
                    ],
                    'ro1' => 'Optometris Dian',
                    'ro2' => 'Teknisi Rudi',
                    'new_prescription' => 1,
                    'prescription' => [
                        'doctor_or_optician' => 'Dr. Robert Sp.M',
                        'source' => 'in_store',
                        'od_lens_id' => $this->lens->id,
                        'od_qty' => 1,
                        'os_lens_id' => $secondLens->id,
                        'os_qty' => 1,
                        'od_sph' => '-1.25', 'od_cyl' => '-0.50', 'od_axis' => '90',
                        'od_add' => '+1.00', 'od_pd' => '62',
                        'os_sph' => '-1.00', 'os_cyl' => '-0.25', 'os_axis' => '85',
                        'os_add' => '+1.00', 'os_pd' => '60',
                        'notes' => 'Mata lelah saat membaca.',
                    ],
                ],
            ],
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'processing',
        ])->assertRedirect();

        $transaction = Transaction::firstOrFail();

        $this->assertSame(6000000.0, (float) $transaction->grandtotal);

        $right = $transaction->prescriptions()->where('sisi', 'R')->firstOrFail();
        $left = $transaction->prescriptions()->where('sisi', 'L')->firstOrFail();
        $rightLine = $transaction->items()->where('item_type', 'lens')->where('itemno', (string) $this->lens->id)->firstOrFail();
        $leftLine = $transaction->items()->where('item_type', 'lens')->where('itemno', (string) $secondLens->id)->firstOrFail();

        // resep R & L menempel pada baris lensa masing-masing sisi
        $this->assertSame($rightLine->sortno, $right->sortno);
        $this->assertSame($leftLine->sortno, $left->sortno);
        $this->assertSame((string) $this->lens->id, $right->itemno_lensa);
        $this->assertSame((string) $secondLens->id, $left->itemno_lensa);
        $this->assertSame(1, $right->qty);
        $this->assertSame(1, $left->qty);

        $this->assertSame('in_store', $right->source);
        $this->assertSame('in_store', $left->source);
        $this->assertSame('Dr. Robert Sp.M', $right->resep);
        $this->assertSame('-1.25', $right->sph);
        $this->assertSame('-0.50', $right->cyl);
        $this->assertSame('90', $right->axis);
        $this->assertSame('+1.00', $right->add);
        $this->assertSame('62', $right->pd);
        $this->assertSame('-1.00', $left->sph);
        $this->assertSame('60', $left->pd);
        $this->assertSame('Optometris Dian', $right->ro1);
        $this->assertSame('Teknisi Rudi', $right->ro2);
        $this->assertSame('Mata lelah saat membaca.', $right->ket);

        $this->assertSame(4, $this->frame->fresh()->stock);
        $this->assertSame(9, $this->lens->fresh()->stock);
        $this->assertSame(5, $secondLens->fresh()->stock);

        $this->actingAs($this->staff)->get(route('transactions.show', $transaction))
            ->assertOk()
            ->assertSee('In Store')
            ->assertSee($secondLens->name);

        $this->actingAs($this->staff)->get(route('transactions.print', $transaction))
            ->assertOk()
            ->assertSee('In Store');
    }

    public function test_pos_reuses_saved_prescription_with_source_and_lens_reference(): void
    {
        $savedRx = $this->seedSavedPrescription('Dr. Existing');

        $this->actingAs($this->staff)->post(route('pos.store'), [
            'customer_id' => $this->customer->id,
            'users' => [
                [
                    'name' => 'Adi',
                    'items' => [['type' => 'lens', 'id' => $this->lens->id, 'qty' => 2]],
                    'rx_ref' => $savedRx,
                    'ro1' => 'Optometris Dian',
                    'ro2' => 'Teknisi Rudi',
                ],
            ],
            'payment_method' => 'qris',
            'payment_status' => 'paid',
            'status' => 'ordered',
        ])->assertRedirect();

        $transaction = Transaction::latest('sales_no')->firstOrFail();
        $line = $transaction->items()->firstOrFail();

        $right = $transaction->prescriptions()->where('sisi', 'R')->firstOrFail();
        $left = $transaction->prescriptions()->where('sisi', 'L')->firstOrFail();

        $this->assertSame('bring_own', $right->source);
        $this->assertSame('bring_own', $left->source);
        $this->assertSame('Dr. Existing', $right->resep);
        $this->assertSame('-1.00', $right->sph);
        $this->assertSame('-0.75', $left->sph);
        $this->assertSame('62', $right->pd);
        $this->assertSame((string) $this->lens->id, $right->itemno_lensa);
        $this->assertSame($line->sortno, $right->sortno);
        $this->assertSame('Optometris Dian', $right->ro1);

        $this->actingAs($this->staff)->get(route('transactions.show', $transaction))
            ->assertOk()
            ->assertSee('Bawa Resep Sendiri');
    }

    public function test_pos_quick_customer_endpoint(): void
    {
        $response = $this->actingAs($this->staff)->postJson(route('pos.customers'), [
            'name' => 'Pelanggan Baru', 'phone' => '081777777777',
        ]);

        $response->assertCreated()->assertJsonStructure(['id', 'name', 'member_id']);
        $this->assertDatabaseHas('customers', ['phone' => '081777777777']);
    }

    public function test_pos_creates_one_transaction_for_multiple_wearers(): void
    {
        $savedRx = $this->seedSavedPrescription('Dr. Existing');

        $this->actingAs($this->staff)->post(route('pos.store'), [
            'customer_id' => $this->customer->id,
            'users' => [
                ['name' => 'Adi', 'items' => [['type' => 'frame', 'id' => $this->frame->id, 'qty' => 1]]],
                [
                    'name' => 'Anak 1',
                    'items' => [
                        ['type' => 'frame', 'id' => $this->frame->id, 'qty' => 1],
                        ['type' => 'lens', 'id' => $this->lens->id, 'qty' => 2],
                    ],
                    'new_prescription' => 1,
                    'ro1' => 'Optometris Dian',
                    'ro2' => 'Teknisi Rudi',
                    'prescription' => [
                        'doctor_or_optician' => 'Dr. Robert Sp.M',
                        'od_sph' => '-1.50',
                        'os_sph' => '-1.25',
                    ],
                ],
                [
                    'name' => 'Anak 2',
                    'items' => [['type' => 'lens', 'id' => $this->lens->id, 'qty' => 1]],
                    'rx_ref' => $savedRx,
                ],
            ],
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'processing',
        ])->assertRedirect();

        $transaction = Transaction::latest('sales_no')->firstOrFail();

        $this->assertSame(2, Transaction::count());
        $this->assertSame(
            ['Adi', 'Anak 1', 'Anak 2'],
            array_values(array_unique($transaction->items()->orderBy('sortno')->pluck('username')->all()))
        );

        $this->assertSame(9500000.0, (float) $transaction->grandtotal);
        $this->assertSame(3, $this->frame->fresh()->stock);
        $this->assertSame(7, $this->lens->fresh()->stock);

        $adiSortno = $transaction->items()->where('username', 'Adi')->value('sortno');
        $this->assertSame(0, $transaction->prescriptions()->where('sortno', $adiSortno)->count());

        $childOneRx = $transaction->prescriptions()->where('sisi', 'R')->firstOrFail();
        $this->assertSame('Dr. Robert Sp.M', $childOneRx->resep);
        $this->assertSame('-1.50', $childOneRx->sph);
        $this->assertSame('Optometris Dian', $childOneRx->ro1);
        $this->assertSame('Teknisi Rudi', $childOneRx->ro2);

        $childTwoRx = $transaction->prescriptions()
            ->where('resep', 'Dr. Existing')->where('sisi', 'R')->firstOrFail();
        $this->assertSame('-1.00', $childTwoRx->sph);
        $this->assertSame('lens', $transaction->items()->where('username', 'Anak 2')->first()->item_type);

        $this->assertSame(4, $transaction->items()->count());
        $this->assertSame(1, Payment::where('sales_no', $transaction->sales_no)->count());

        $this->actingAs($this->staff)->get(route('transactions.show', $transaction))
            ->assertOk()
            ->assertSee('Anak 1')
            ->assertSee('Pemakai');

        $this->actingAs($this->staff)->get(route('transactions.print', $transaction))
            ->assertOk()
            ->assertSee('Anak 2');
    }

    public function test_pos_requires_prescription_for_wearer_who_picks_lens(): void
    {
        $this->actingAs($this->staff)->post(route('pos.store'), [
            'customer_id' => $this->customer->id,
            'users' => [
                ['name' => 'Adi', 'items' => [['type' => 'lens', 'id' => $this->lens->id, 'qty' => 1]]],
            ],
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'ordered',
        ])->assertSessionHasErrors('users.0.rx_ref');

        $this->assertSame(0, Transaction::count());
        $this->assertSame(10, $this->lens->fresh()->stock);
    }

    public function test_pos_rejects_wearer_without_name_or_items(): void
    {
        $this->actingAs($this->staff)->post(route('pos.store'), [
            'customer_id' => $this->customer->id,
            'users' => [
                ['name' => '', 'items' => [['type' => 'frame', 'id' => $this->frame->id, 'qty' => 1]]],
            ],
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'ordered',
        ])->assertSessionHasErrors('users.0.name');

        $this->actingAs($this->staff)->post(route('pos.store'), [
            'customer_id' => $this->customer->id,
            'users' => [['name' => 'Adi', 'items' => []]],
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'ordered',
        ])->assertSessionHasErrors('users.0.items');

        $this->assertSame(0, Transaction::count());
    }

    public function test_pos_can_sell_accessory_item(): void
    {
        $category = ProductCategory::create(['name' => 'Softlens', 'slug' => 'softlens', 'type' => 'accessory', 'is_active' => true]);
        $accessory = Accessory::create([
            'sku' => 'SL-001', 'name' => 'Acuvue Moist Daily', 'brand' => 'Acuvue',
            'category_id' => $category->id, 'buy_price' => 120000, 'sell_price' => 225000,
            'stock' => 20, 'min_stock' => 5, 'status' => 'active',
        ]);

        $this->actingAs($this->staff)->post(route('pos.store'), [
            'customer_id' => $this->customer->id,
            'users' => [
                ['name' => 'Adi', 'items' => [['type' => 'accessory', 'id' => $accessory->id, 'qty' => 2]]],
            ],
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'completed',
        ])->assertRedirect();

        $transaction = Transaction::firstOrFail();

        $this->assertSame(450000.0, (float) $transaction->grandtotal);
        $this->assertSame(18, $accessory->fresh()->stock);
        $this->assertSame((string) $accessory->id, $transaction->items()->first()->itemno);
        $this->assertSame('accessory', $transaction->items()->first()->item_type);
    }

    public function test_transaction_status_can_be_updated(): void
    {
        $transaction = $this->makeTransaction(['sales_status' => 'ordered']);

        $this->actingAs($this->staff)
            ->patch(route('transactions.status', $transaction), ['status' => 'ready'])
            ->assertSessionHas('success');

        $this->assertSame('ready', $transaction->fresh()->sales_status);

        $this->actingAs($this->staff)
            ->patch(route('transactions.status', $transaction), ['status' => 'selesai'])
            ->assertSessionHasErrors('status');
    }

    public function test_pos_wearer_stores_ro_names_and_prescription(): void
    {
        $this->actingAs($this->staff)->post(route('pos.store'), [
            'customer_id' => $this->customer->id,
            'users' => [
                [
                    'name' => 'Bambang Sutopo',
                    'items' => [
                        ['type' => 'frame', 'id' => $this->frame->id, 'qty' => 1],
                        ['type' => 'lens', 'id' => $this->lens->id, 'qty' => 1],
                    ],
                    'ro1' => 'Optometris Sari',
                    'ro2' => 'Teknisi Rudi',
                    'new_prescription' => 1,
                    'prescription' => [
                        'doctor_or_optician' => 'Optometris Sari',
                        'od_sph' => '-1.25',
                    ],
                ],
                [
                    'name' => 'Anak 1',
                    'items' => [['type' => 'frame', 'id' => $this->frame->id, 'qty' => 1]],
                    'ro1' => '   ',
                    'ro2' => 'Teknisi Rudi',
                ],
            ],
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'ordered',
        ])->assertRedirect();

        $transaction = Transaction::firstOrFail();

        $rx = $transaction->prescriptions()->where('sisi', 'R')->firstOrFail();
        $this->assertSame('Optometris Sari', $rx->ro1);
        $this->assertSame('Teknisi Rudi', $rx->ro2);
        $this->assertSame('Optometris Sari', $rx->resep);

        $wearerSortnos = $transaction->items()->where('username', 'Anak 1')->pluck('sortno');
        $this->assertSame(0, $transaction->prescriptions()->whereIn('sortno', $wearerSortnos)->count());

        $this->actingAs($this->staff)->get(route('transactions.show', $transaction))
            ->assertOk()
            ->assertSee('RO1 · Periksa Mata')
            ->assertSee('Optometris Sari');
    }

    public function test_transaction_group_ro_can_be_updated(): void
    {
        $this->actingAs($this->staff)->post(route('pos.store'), [
            'customer_id' => $this->customer->id,
            'users' => [
                [
                    'name' => 'Adi',
                    'items' => [['type' => 'lens', 'id' => $this->lens->id, 'qty' => 1]],
                    'new_prescription' => 1,
                    'prescription' => ['doctor_or_optician' => 'Dr. Robert Sp.M', 'od_sph' => '-1.00'],
                ],
            ],
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'status' => 'ordered',
        ])->assertRedirect();

        $transaction = Transaction::firstOrFail();

        $this->actingAs($this->staff)
            ->patch(route('transactions.group.update', $transaction), [
                'username' => 'Adi',
                'ro1' => 'Optometris Sari',
                'ro2' => '',
            ])
            ->assertSessionHas('success');

        $rx = $transaction->prescriptions()->where('sisi', 'R')->firstOrFail();
        $this->assertSame('Optometris Sari', $rx->ro1);
        $this->assertNull($rx->ro2);

        $this->actingAs($this->staff)
            ->patch(route('transactions.group.update', $transaction), ['ro1' => 'Optometris Sari'])
            ->assertSessionHasErrors('username');
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

        $transaction = Transaction::firstOrFail();

        $this->actingAs($this->staff)->get(route('transactions.show', $transaction))
            ->assertOk()
            ->assertSee($transaction->sales_no);

        $this->actingAs($this->staff)->get(route('transactions.print', $transaction))
            ->assertOk()
            ->assertSee('TOTAL');
    }

    /**
     * Resep lama pada transaksi sebelumnya, dikembalikan sebagai ref "sales_no:sortno".
     */
    private function seedSavedPrescription(string $doctor): string
    {
        $trx = $this->makeTransaction([
            'sales_no' => 'TRX-'.now()->subDay()->format('Ymd').'-00001',
            'sales_date' => now()->subDay()->toDateString(),
            'sales_time' => '10:00:00',
        ]);

        TransactionItem::create([
            'sales_no' => $trx->sales_no,
            'sortno' => 1,
            'itemno' => (string) $this->lens->id,
            'item_type' => 'lens',
            'item_description' => $this->lens->name,
            'username' => 'Pemakai Lama',
            'qty' => 1,
            'unit' => 'pcs',
            'price' => $this->lens->sell_price,
            'disc_percent' => 0,
            'subtotal' => $this->lens->sell_price,
        ]);

        foreach (['R' => '-1.00', 'L' => '-0.75'] as $sisi => $sph) {
            $trx->prescriptions()->create([
                'sortno' => 1,
                'item_type' => 'lens',
                'itemno_lensa' => (string) $this->lens->id,
                'qty' => 1,
                'sisi' => $sisi,
                'sph' => $sph,
                'cyl' => '-0.50',
                'axis' => '90',
                'add' => '+1.00',
                'pd' => $sisi === 'R' ? '62' : '60',
                'resep' => $doctor,
                'source' => 'bring_own',
                'ro1' => 'Optometris Lama',
                'ro2' => 'Teknisi Lama',
            ]);
        }

        return $trx->sales_no.':1';
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makeTransaction(array $attributes = []): Transaction
    {
        return Transaction::create($attributes + [
            'sales_no' => 'TRX-'.now()->format('Ymd').'-'.uniqid(),
            'sales_date' => now()->toDateString(),
            'sales_time' => now()->format('H:i:s'),
            'customer_id' => $this->customer->id,
            'input_by' => $this->staff->name,
            'payment_by' => 'cash',
            'grandtotal' => 100000,
            'sales_status' => 'ordered',
        ]);
    }
}
