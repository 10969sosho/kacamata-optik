<?php

namespace Database\Seeders;

use App\Models\Accessory;
use App\Models\Customer;
use App\Models\Frame;
use App\Models\Lens;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\ProductCategory;
use App\Models\Promotion;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\TransactionUser;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $pusat = Store::create([
            'code' => 'OPT-PST',
            'name' => 'Kacamata Optik Pusat',
            'phone' => '0215550123',
            'address' => 'Jl. MH Thamrin No. 10, Jakarta Pusat',
            'is_active' => true,
        ]);

        Store::create([
            'code' => 'OPT-SLT',
            'name' => 'Kacamata Optik Cabang Selatan',
            'phone' => '0224440987',
            'address' => 'Jl. Asia Afrika No. 25, Bandung',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Admin Optik',
            'email' => 'admin@optik.com',
            'phone' => '081234567890',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $staff = User::create([
            'name' => 'Staff Optik',
            'email' => 'staff@optik.com',
            'phone' => '081234567891',
            'password' => 'password',
            'role' => 'staff',
            'store_id' => $pusat->id,
            'status' => 'active',
        ]);

        $budiUser = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@gmail.com',
            'phone' => '081298765432',
            'password' => 'password',
            'role' => 'customer',
            'status' => 'active',
        ]);

        $budi = Customer::create([
            'user_id' => $budiUser->id,
            'member_id' => 'KCM-000012',
            'name' => 'Budi Santoso',
            'phone' => '081298765432',
            'email' => 'budi@gmail.com',
            'birth_date' => '1990-05-12',
            'gender' => 'Laki-laki',
            'address' => 'Jl. Sudirman No. 45',
            'registered_at' => '2025-03-10',
            'status' => 'active',
        ]);

        $siti = Customer::create([
            'member_id' => 'KCM-000013',
            'name' => 'Siti Rahma',
            'phone' => '081298765433',
            'email' => 'siti@gmail.com',
            'birth_date' => '1992-11-03',
            'gender' => 'Perempuan',
            'address' => 'Jl. Melati No. 12, Bandung',
            'registered_at' => '2025-05-20',
            'status' => 'active',
        ]);

        $hendra = Customer::create([
            'member_id' => 'KCM-000014',
            'name' => 'Hendra Wijaya',
            'phone' => '081298765434',
            'email' => 'hendra@gmail.com',
            'birth_date' => '1985-07-21',
            'gender' => 'Laki-laki',
            'address' => 'Jl. Kenanga No. 8, Jakarta Selatan',
            'registered_at' => '2025-08-02',
            'status' => 'active',
        ]);

        Customer::create([
            'member_id' => 'KCM-000015',
            'name' => 'Maya Anggraini',
            'phone' => '081298765435',
            'birth_date' => '1996-02-28',
            'gender' => 'Perempuan',
            'registered_at' => '2026-01-15',
            'status' => 'active',
        ]);

        ProductCategory::create([
            'name' => 'Frame Kacamata',
            'slug' => 'frame-kacamata',
            'type' => 'frame',
            'is_active' => true,
        ]);

        $catSingleVision = ProductCategory::create([
            'name' => 'Lensa Single Vision',
            'slug' => 'lensa-single-vision',
            'type' => 'lens',
            'is_active' => true,
        ]);

        $catProgresif = ProductCategory::create([
            'name' => 'Lensa Progresif',
            'slug' => 'lensa-progresif',
            'type' => 'lens',
            'is_active' => true,
        ]);

        $catBlueLight = ProductCategory::create([
            'name' => 'Lensa Blue Light',
            'slug' => 'lensa-blue-light',
            'type' => 'lens',
            'is_active' => true,
        ]);

        ProductCategory::create([
            'name' => 'Aksesoris',
            'slug' => 'aksesoris',
            'type' => 'accessory',
            'is_active' => true,
        ]);

        ProductCategory::create([
            'name' => 'Softlens',
            'slug' => 'softlens',
            'type' => 'accessory',
            'is_active' => true,
        ]);

        ProductCategory::create([
            'name' => 'Case / Wadah',
            'slug' => 'case-wadah',
            'type' => 'accessory',
            'is_active' => true,
        ]);

        $frames = collect([
            [
                'sku' => 'FR-RB-001',
                'name' => 'Ray-Ban Aviator Classic',
                'brand' => 'Ray-Ban',
                'model' => 'RB3025',
                'color' => 'Gold/Green',
                'size' => '58-14-135',
                'buy_price' => 1200000,
                'sell_price' => 2500000,
                'stock' => 12,
                'min_stock' => 5,
            ],
            [
                'sku' => 'FR-RB-002',
                'name' => 'Ray-Ban Wayfarer',
                'brand' => 'Ray-Ban',
                'model' => 'RB2140',
                'color' => 'Black',
                'size' => '50-22-150',
                'buy_price' => 1100000,
                'sell_price' => 2200000,
                'stock' => 8,
                'min_stock' => 5,
            ],
            [
                'sku' => 'FR-OK-001',
                'name' => 'Oakley Holbrook',
                'brand' => 'Oakley',
                'model' => 'OO9102',
                'color' => 'Matte Black',
                'size' => '55-18-137',
                'buy_price' => 950000,
                'sell_price' => 1950000,
                'stock' => 15,
                'min_stock' => 5,
            ],
            [
                'sku' => 'FR-GM-001',
                'name' => 'Gentle Monster South Side',
                'brand' => 'Gentle Monster',
                'model' => 'SS-01',
                'color' => 'Glossy Black',
                'size' => '48-21-152',
                'buy_price' => 1600000,
                'sell_price' => 3400000,
                'stock' => 3,
                'min_stock' => 5,
            ],
            [
                'sku' => 'FR-GC-001',
                'name' => 'Gucci Square Frame',
                'brand' => 'Gucci',
                'model' => 'GG0010S',
                'color' => 'Havana',
                'size' => '54-17-145',
                'buy_price' => 2200000,
                'sell_price' => 4800000,
                'stock' => 2,
                'min_stock' => 5,
            ],
        ])->mapWithKeys(fn (array $frame) => [
            $frame['sku'] => Frame::create($frame + [
                'gender' => 'Unisex',
                'status' => 'active',
                'description' => "{$frame['brand']} {$frame['name']} — {$frame['color']} {$frame['size']}.",
            ]),
        ]);

        $lenses = collect([
            [
                'sku' => 'LS-ES-001',
                'brand' => 'Essilor',
                'name' => 'Crizal Easy Pro 1.56',
                'category_id' => $catSingleVision->id,
                'lens_type' => 'Anti Reflective',
                'index_val' => '1.56',
                'coating' => 'Anti Reflective & Scratch',
                'buy_price' => 700000,
                'sell_price' => 1500000,
                'stock' => 25,
                'min_stock' => 10,
                'supplier' => 'Essilor Indonesia',
            ],
            [
                'sku' => 'LS-ES-002',
                'brand' => 'Essilor',
                'name' => 'Eyezen Start 1.60',
                'category_id' => $catBlueLight->id,
                'lens_type' => 'Blue Light',
                'index_val' => '1.60',
                'coating' => 'Crizal Prevencia',
                'buy_price' => 1100000,
                'sell_price' => 2400000,
                'stock' => 4,
                'min_stock' => 10,
                'supplier' => 'Essilor Indonesia',
            ],
            [
                'sku' => 'LS-HY-001',
                'brand' => 'Hoya',
                'name' => 'Hilux Stellify 1.56',
                'category_id' => $catSingleVision->id,
                'lens_type' => 'Single Vision',
                'index_val' => '1.56',
                'coating' => 'HVP',
                'buy_price' => 450000,
                'sell_price' => 950000,
                'stock' => 30,
                'min_stock' => 10,
                'supplier' => 'Hoya Lens',
            ],
            [
                'sku' => 'LS-ZS-001',
                'brand' => 'Zeiss',
                'name' => 'DriveSafe Progressive 1.67',
                'category_id' => $catProgresif->id,
                'lens_type' => 'Progressive',
                'index_val' => '1.67',
                'coating' => 'DuraVision DriveSafe',
                'buy_price' => 2500000,
                'sell_price' => 5200000,
                'stock' => 6,
                'min_stock' => 5,
                'supplier' => 'Carl Zeiss',
            ],
            [
                'sku' => 'LS-PL-001',
                'brand' => 'Rodenstock',
                'name' => 'ColorMatic Sun 1.59',
                'category_id' => $catSingleVision->id,
                'lens_type' => 'Polarized',
                'index_val' => '1.59',
                'coating' => 'Solitaire Protect',
                'buy_price' => 900000,
                'sell_price' => 1850000,
                'stock' => 14,
                'min_stock' => 5,
                'supplier' => 'Rodenstock',
            ],
        ])->mapWithKeys(fn (array $lens) => [$lens['sku'] => Lens::create($lens)]);

        $resepBudi = Prescription::create([
            'customer_id' => $budi->id,
            'doctor_or_optician' => 'Dr. Robert Sp.M',
            'examination_date' => '2026-08-15',
            'prescription_type' => 'Distance',
            'od_sph' => '-1.25',
            'od_cyl' => '-0.50',
            'od_axis' => '90',
            'od_add' => '0',
            'od_pd' => '31.5',
            'os_sph' => '-1.00',
            'os_cyl' => '-0.25',
            'os_axis' => '80',
            'os_add' => '0',
            'os_pd' => '31.5',
            'pd_total' => '63',
            'fitting_height' => '18',
            'notes' => 'Pengguna lensa single vision, disarankan anti reflective.',
        ]);

        $resepSiti = Prescription::create([
            'customer_id' => $siti->id,
            'doctor_or_optician' => 'Optometris Dian',
            'examination_date' => '2026-09-01',
            'prescription_type' => 'Progressive',
            'od_sph' => '-2.50',
            'od_cyl' => '-1.00',
            'od_axis' => '180',
            'od_add' => '+1.50',
            'od_pd' => '32',
            'os_sph' => '-2.75',
            'os_cyl' => '-0.75',
            'os_axis' => '175',
            'os_add' => '+1.50',
            'os_pd' => '31',
            'pd_total' => '63',
            'notes' => 'Butuh lensa progresif untuk jarak jauh dan dekat.',
        ]);

        $promoMerdeka = Promotion::create([
            'name' => 'Diskon Merdeka 15%',
            'description' => 'Diskon 15% untuk pembelian minimal Rp 1.000.000.',
            'promo_type' => 'percentage',
            'discount_value' => 15,
            'min_spend' => 1000000,
            'start_date' => '2026-08-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);

        $promoFrame = Promotion::create([
            'name' => 'Potongan Frame Rp 150.000',
            'description' => 'Potongan harga Rp 150.000 untuk pembelian minimal Rp 1.500.000.',
            'promo_type' => 'nominal',
            'discount_value' => 150000,
            'min_spend' => 1500000,
            'start_date' => '2026-09-01',
            'end_date' => '2026-10-31',
            'is_active' => true,
        ]);

        Promotion::create([
            'name' => 'Promo Khusus Member VIP',
            'description' => 'Diskon 10% khusus member VIP dengan minimum transaksi Rp 500.000.',
            'promo_type' => 'member_only',
            'discount_value' => 10,
            'min_spend' => 500000,
            'is_active' => true,
        ]);

        $trx1 = Transaction::create([
            'invoice_number' => 'TRX-20260920-00123',
            'store_id' => $pusat->id,
            'staff_id' => $staff->id,
            'customer_id' => $budi->id,
            'prescription_id' => $resepBudi->id,
            'subtotal' => 4000000,
            'discount_amount' => 600000,
            'promo_id' => $promoMerdeka->id,
            'total_amount' => 3400000,
            'payment_status' => 'paid',
            'payment_method' => 'transfer',
            'status' => 'completed',
            'notes' => 'Diskon Merdeka 15% otomatis diterapkan.',
            'transaction_date' => '2026-09-20 14:30:00',
        ]);

        TransactionItem::create([
            'transaction_id' => $trx1->id,
            'item_type' => 'frame',
            'frame_id' => $frames['FR-RB-001']->id,
            'name' => 'Ray-Ban Aviator Classic',
            'quantity' => 1,
            'price' => 2500000,
            'discount' => 0,
            'subtotal' => 2500000,
        ]);

        TransactionItem::create([
            'transaction_id' => $trx1->id,
            'item_type' => 'lens',
            'lens_id' => $lenses['LS-ES-001']->id,
            'name' => 'Essilor Crizal Easy Pro 1.56',
            'quantity' => 1,
            'price' => 1500000,
            'discount' => 0,
            'subtotal' => 1500000,
        ]);

        Payment::create([
            'transaction_id' => $trx1->id,
            'amount' => 3400000,
            'payment_method' => 'transfer',
            'reference_number' => 'TRF-20260920-8891',
            'paid_at' => '2026-09-20 14:35:00',
            'note' => 'Pelunasan penuh via transfer bank.',
        ]);

        $trx2 = Transaction::create([
            'invoice_number' => 'TRX-20260925-00145',
            'store_id' => $pusat->id,
            'staff_id' => $staff->id,
            'customer_id' => $siti->id,
            'prescription_id' => $resepSiti->id,
            'subtotal' => 8600000,
            'discount_amount' => 150000,
            'promo_id' => $promoFrame->id,
            'total_amount' => 8450000,
            'payment_status' => 'down_payment',
            'payment_method' => 'qris',
            'status' => 'processing',
            'notes' => 'DP Rp 5.000.000 via QRIS, sisa dibayar saat pengambilan.',
            'transaction_date' => '2026-09-25 11:00:00',
        ]);

        TransactionItem::create([
            'transaction_id' => $trx2->id,
            'item_type' => 'frame',
            'frame_id' => $frames['FR-GM-001']->id,
            'name' => 'Gentle Monster South Side',
            'quantity' => 1,
            'price' => 3400000,
            'discount' => 0,
            'subtotal' => 3400000,
        ]);

        TransactionItem::create([
            'transaction_id' => $trx2->id,
            'item_type' => 'lens',
            'lens_id' => $lenses['LS-ZS-001']->id,
            'name' => 'Zeiss DriveSafe Progressive 1.67',
            'quantity' => 1,
            'price' => 5200000,
            'discount' => 0,
            'subtotal' => 5200000,
        ]);

        Payment::create([
            'transaction_id' => $trx2->id,
            'amount' => 5000000,
            'payment_method' => 'qris',
            'reference_number' => 'QRIS-20260925-4471',
            'paid_at' => '2026-09-25 11:05:00',
            'note' => 'Uang muka (DP) Rp 5.000.000.',
        ]);

        $trx3 = Transaction::create([
            'invoice_number' => 'TRX-20260926-00150',
            'store_id' => $pusat->id,
            'staff_id' => $staff->id,
            'customer_id' => $hendra->id,
            'prescription_id' => null,
            'subtotal' => 2900000,
            'discount_amount' => 0,
            'promo_id' => null,
            'total_amount' => 2900000,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'status' => 'ready',
            'notes' => 'Kacamata siap diambil di toko pusat.',
            'transaction_date' => '2026-09-26 10:15:00',
        ]);

        TransactionItem::create([
            'transaction_id' => $trx3->id,
            'item_type' => 'frame',
            'frame_id' => $frames['FR-OK-001']->id,
            'name' => 'Oakley Holbrook',
            'quantity' => 1,
            'price' => 1950000,
            'discount' => 0,
            'subtotal' => 1950000,
        ]);

        TransactionItem::create([
            'transaction_id' => $trx3->id,
            'item_type' => 'lens',
            'lens_id' => $lenses['LS-HY-001']->id,
            'name' => 'Hoya Hilux Stellify 1.56',
            'quantity' => 1,
            'price' => 950000,
            'discount' => 0,
            'subtotal' => 950000,
        ]);

        Payment::create([
            'transaction_id' => $trx3->id,
            'amount' => 2900000,
            'payment_method' => 'cash',
            'paid_at' => '2026-09-26 10:20:00',
            'note' => 'Pelunasan tunai di kasir.',
        ]);

        $this->seedAdditionalCatalog($frames, $lenses);
        $extraCustomers = $this->seedAdditionalCustomers();
        $this->seedAdditionalPrescriptions();
        $this->seedAdditionalPromotions();
        $this->seedAdditionalTransactions($frames, $lenses, $extraCustomers);

        $this->attachWearer($trx1, $budi->name, $trx1->prescription_id);
        $this->attachWearer($trx2, $siti->name, $trx2->prescription_id);
        $this->attachWearer($trx3, $hendra->name);

        $this->seedMultiWearerTransaction($pusat, $staff, $budi, $resepBudi);
    }

    /**
     * Pasang pemakai (user) pada transaksi dan tautkan seluruh itemnya.
     */
    private function attachWearer(Transaction $transaction, string $name, ?int $prescriptionId = null): void
    {
        $wearer = TransactionUser::create([
            'transaction_id' => $transaction->id,
            'name' => $name,
            'prescription_id' => $prescriptionId,
        ]);

        TransactionItem::where('transaction_id', $transaction->id)
            ->whereNull('transaction_user_id')
            ->update(['transaction_user_id' => $wearer->id]);
    }

    /**
     * Contoh 1 transaksi / 1 member id dengan beberapa pemakai
     * (Ayah, Anak 1, Anak 2) — masing-masing item & resep sendiri.
     */
    private function seedMultiWearerTransaction(Store $store, User $staff, Customer $customer, ?Prescription $customerPrescription): void
    {
        $resepAnak1 = Prescription::create([
            'customer_id' => $customer->id,
            'doctor_or_optician' => 'Optometris Dian',
            'examination_date' => '2026-09-10',
            'od_sph' => '-2.50',
            'od_cyl' => '-0.75',
            'od_axis' => '180',
            'os_sph' => '-2.25',
            'os_cyl' => '-0.50',
            'os_axis' => '175',
            'pd_total' => '58',
            'notes' => 'Resep anak pertama.',
        ]);

        $resepAnak2 = Prescription::create([
            'customer_id' => $customer->id,
            'doctor_or_optician' => 'Optometris Dian',
            'examination_date' => '2026-09-10',
            'od_sph' => '-1.75',
            'os_sph' => '-1.50',
            'pd_total' => '56',
            'notes' => 'Resep anak kedua.',
        ]);

        $plan = [
            ['name' => $customer->name, 'rx' => $customerPrescription?->id, 'items' => [['frame', 'FR-RB-002', 1], ['lens', 'LS-ES-002', 1]]],
            ['name' => 'Raka (Anak 1)', 'rx' => $resepAnak1->id, 'items' => [['frame', 'FR-KD-001', 1], ['lens', 'LS-HY-001', 1]]],
            ['name' => 'Rini (Anak 2)', 'rx' => $resepAnak2->id, 'items' => [['lens', 'LS-ES-001', 1]]],
        ];

        $groups = [];
        $subtotal = 0.0;

        foreach ($plan as $group) {
            $lines = [];

            foreach ($group['items'] as [$type, $sku, $qty]) {
                $product = $type === 'frame'
                    ? Frame::query()->where('sku', $sku)->first()
                    : Lens::query()->where('sku', $sku)->first();

                if (! $product) {
                    continue;
                }

                $price = (float) $product->sell_price;
                $subtotal += $price * $qty;
                $lines[] = ['type' => $type, 'product' => $product, 'qty' => $qty, 'price' => $price];
            }

            $groups[] = ['name' => $group['name'], 'rx' => $group['rx'], 'lines' => $lines];
        }

        if ($subtotal <= 0) {
            return;
        }

        $transaction = Transaction::create([
            'invoice_number' => 'TRX-'.now()->format('Ymd').'-00200',
            'store_id' => $store->id,
            'staff_id' => $staff->id,
            'customer_id' => $customer->id,
            'prescription_id' => $customerPrescription?->id,
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'total_amount' => $subtotal,
            'payment_status' => 'down_payment',
            'payment_method' => 'cash',
            'status' => 'ordered',
            'notes' => 'Satu transaksi untuk ayah & dua anaknya.',
            'transaction_date' => now()->subDay()->setTime(9, 30),
        ]);

        foreach ($groups as $group) {
            $wearer = TransactionUser::create([
                'transaction_id' => $transaction->id,
                'name' => $group['name'],
                'prescription_id' => $group['rx'],
            ]);

            foreach ($group['lines'] as $line) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'transaction_user_id' => $wearer->id,
                    'item_type' => $line['type'],
                    'frame_id' => $line['type'] === 'frame' ? $line['product']->id : null,
                    'lens_id' => $line['type'] === 'lens' ? $line['product']->id : null,
                    'name' => $line['product']->name,
                    'quantity' => $line['qty'],
                    'price' => $line['price'],
                    'discount' => 0,
                    'subtotal' => $line['price'] * $line['qty'],
                ]);
            }
        }

        Payment::create([
            'transaction_id' => $transaction->id,
            'amount' => (int) round($subtotal / 2, -3),
            'payment_method' => 'cash',
            'paid_at' => now()->subDay()->setTime(10, 0),
            'note' => 'DP 50%.',
        ]);
    }

    /**
     * Katalog tambahan agar master produk & alert low stock lebih representatif.
     *
     * @param  Collection<string, Frame>  $frames
     * @param  Collection<string, Lens>  $lenses
     */
    private function seedAdditionalCatalog($frames, $lenses): void
    {
        $extraFrames = [
            ['sku' => 'FR-TF-001', 'name' => 'Tom Ford FT5401', 'brand' => 'Tom Ford', 'model' => 'FT5401', 'color' => 'Black', 'size' => '54-16-145', 'gender' => 'Pria', 'buy_price' => 2600000, 'sell_price' => 5500000, 'stock' => 4, 'min_stock' => 3],
            ['sku' => 'FR-PR-001', 'name' => 'Prada PR17WS', 'brand' => 'Prada', 'model' => 'PR17WS', 'color' => 'Havana', 'size' => '52-18-140', 'gender' => 'Wanita', 'buy_price' => 2400000, 'sell_price' => 4900000, 'stock' => 3, 'min_stock' => 3],
            ['sku' => 'FR-VE-001', 'name' => 'Versace VE4410', 'brand' => 'Versace', 'model' => 'VE4410', 'color' => 'Gold', 'size' => '55-16-140', 'gender' => 'Unisex', 'buy_price' => 2300000, 'sell_price' => 4750000, 'stock' => 5, 'min_stock' => 3],
            ['sku' => 'FR-NK-001', 'name' => 'Nike EV0990 Sport', 'brand' => 'Nike', 'model' => 'EV0990', 'color' => 'Grey', 'size' => '54-16-135', 'gender' => 'Unisex', 'buy_price' => 480000, 'sell_price' => 950000, 'stock' => 18, 'min_stock' => 6],
            ['sku' => 'FR-CK-001', 'name' => 'Calvin Klein CK5238', 'brand' => 'Calvin Klein', 'model' => 'CK5238', 'color' => 'Rose Gold', 'size' => '52-17-145', 'gender' => 'Wanita', 'buy_price' => 850000, 'sell_price' => 1750000, 'stock' => 9, 'min_stock' => 4],
            ['sku' => 'FR-OP-001', 'name' => 'Oliver Peoples Cary Grant', 'brand' => 'Oliver Peoples', 'model' => '5200', 'color' => 'Tortoise', 'size' => '50-22-145', 'gender' => 'Pria', 'buy_price' => 3200000, 'sell_price' => 6400000, 'stock' => 2, 'min_stock' => 3],
            ['sku' => 'FR-NS-001', 'name' => 'Nusantara Round Gold', 'brand' => 'Nusantara', 'model' => 'Round', 'color' => 'Gold', 'size' => '48-21-140', 'gender' => 'Unisex', 'buy_price' => 220000, 'sell_price' => 475000, 'stock' => 24, 'min_stock' => 8],
            ['sku' => 'FR-KD-001', 'name' => 'Junior Flex Kids Blue', 'brand' => 'Junior Flex', 'model' => 'Kids', 'color' => 'Blue', 'size' => '44-15-125', 'gender' => 'Anak', 'buy_price' => 160000, 'sell_price' => 375000, 'stock' => 2, 'min_stock' => 5],
        ];

        foreach ($extraFrames as $index => $frame) {
            $id = Frame::max('id') + 1;
            Frame::create($frame + [
                'barcode' => '899'.str_pad((string) $id, 9, '0', STR_PAD_LEFT),
                'status' => 'active',
                'description' => "{$frame['brand']} {$frame['name']} — {$frame['color']} {$frame['size']}.",
            ]);
        }

        unset($index, $id);

        $singleVision = ProductCategory::where('slug', 'lensa-single-vision')->value('id');
        $progresif = ProductCategory::where('slug', 'lensa-progresif')->value('id');

        $extraLenses = [
            ['sku' => 'LS-ZS-002', 'brand' => 'Zeiss', 'name' => 'Single Vision DuraVision 1.67', 'category_id' => $singleVision, 'lens_type' => 'Single Vision', 'index_val' => '1.67', 'coating' => 'DuraVision Platinum', 'buy_price' => 1750000, 'sell_price' => 3500000, 'stock' => 7, 'min_stock' => 5, 'supplier' => 'Carl Zeiss'],
            ['sku' => 'LS-HY-002', 'brand' => 'Hoya', 'name' => 'BlueControl 1.60', 'category_id' => $singleVision, 'lens_type' => 'Blue Light', 'index_val' => '1.60', 'coating' => 'BlueControl', 'buy_price' => 950000, 'sell_price' => 1950000, 'stock' => 3, 'min_stock' => 8, 'supplier' => 'Hoya Lens'],
            ['sku' => 'LS-ES-003', 'brand' => 'Essilor', 'name' => 'Photochromic Transitions 1.60', 'category_id' => $singleVision, 'lens_type' => 'Photochromic', 'index_val' => '1.60', 'coating' => 'Transitions Gen 8', 'buy_price' => 1450000, 'sell_price' => 2900000, 'stock' => 11, 'min_stock' => 5, 'supplier' => 'Essilor Indonesia'],
            ['sku' => 'LS-RD-002', 'brand' => 'Rodenstock', 'name' => 'Bifocal Solitaire 1.56', 'category_id' => $singleVision, 'lens_type' => 'Bifocal', 'index_val' => '1.56', 'coating' => 'Solitaire Protect', 'buy_price' => 780000, 'sell_price' => 1600000, 'stock' => 6, 'min_stock' => 5, 'supplier' => 'Rodenstock'],
            ['sku' => 'LS-PL-002', 'brand' => 'Polarized', 'name' => 'Polarized Gray 1.59', 'category_id' => $singleVision, 'lens_type' => 'Polarized', 'index_val' => '1.59', 'coating' => 'Polarized Gray', 'buy_price' => 650000, 'sell_price' => 1350000, 'stock' => 2, 'min_stock' => 6, 'supplier' => 'PT Optik Sentosa'],
            ['sku' => 'LS-ZS-003', 'brand' => 'Zeiss', 'name' => 'Progressive SmartLife 1.67', 'category_id' => $progresif, 'lens_type' => 'Progressive', 'index_val' => '1.67', 'coating' => 'DuraVision SmartLife', 'buy_price' => 2900000, 'sell_price' => 5900000, 'stock' => 5, 'min_stock' => 4, 'supplier' => 'Carl Zeiss'],
        ];

        foreach ($extraLenses as $lens) {
            Lens::create($lens);
        }

        $this->seedAccessories();

        unset($singleVision, $progresif, $extraFrames, $extraLenses);
    }

    /**
     * Item Master Item di luar frame & lensa: softlens, case/wadah, aksesoris.
     */
    private function seedAccessories(): void
    {
        $softlens = ProductCategory::where('slug', 'softlens')->value('id');
        $case = ProductCategory::where('slug', 'case-wadah')->value('id');
        $aksesoris = ProductCategory::where('slug', 'aksesoris')->value('id');

        $rows = [
            ['sku' => 'SL-AC-001', 'name' => 'Acuvue Moist Daily', 'brand' => 'Acuvue', 'category_id' => $softlens, 'buy_price' => 120000, 'sell_price' => 225000, 'stock' => 40, 'min_stock' => 10, 'description' => 'Softlens harian sekali pakai.'],
            ['sku' => 'SL-CL-002', 'name' => 'Softlens Bulanan Natural', 'brand' => 'Clens', 'category_id' => $softlens, 'buy_price' => 85000, 'sell_price' => 175000, 'stock' => 24, 'min_stock' => 8, 'description' => 'Softlens bulanan warna natural.'],
            ['sku' => 'CS-CL-001', 'name' => 'Case Kacamata Metal', 'brand' => 'Optik', 'category_id' => $case, 'buy_price' => 25000, 'sell_price' => 65000, 'stock' => 35, 'min_stock' => 10, 'description' => 'Wadah kacamata hardcase.'],
            ['sku' => 'CS-CL-002', 'name' => 'Silicone Case + Kain', 'brand' => 'Optik', 'category_id' => $case, 'buy_price' => 12000, 'sell_price' => 35000, 'stock' => 50, 'min_stock' => 15, 'description' => 'Case silikon lengkap dengan kain lap.'],
            ['sku' => 'AC-CL-001', 'name' => 'Tali Kacamata Silicone', 'brand' => 'Everyday', 'category_id' => $aksesoris, 'buy_price' => 8000, 'sell_price' => 25000, 'stock' => 60, 'min_stock' => 20, 'description' => 'Tali penahan kacamata.'],
            ['sku' => 'AC-CL-002', 'name' => 'Cairan Pembersih Lensa 60ml', 'brand' => 'Opticare', 'category_id' => $aksesoris, 'buy_price' => 18000, 'sell_price' => 45000, 'stock' => 3, 'min_stock' => 10, 'description' => 'Solution pembersih lensa harian.'],
        ];

        foreach ($rows as $row) {
            Accessory::create($row + ['status' => 'active']);
        }

        unset($softlens, $case, $aksesoris, $rows);
    }

    /**
     * @return Collection<int, Customer>
     */
    private function seedAdditionalCustomers()
    {
        $rows = [
            ['KCM-000016', 'Rudi Hartono', '081234500011', 'rudi@gmail.com', '1979-01-05', 'Laki-laki', 'Jl. Anggrek Raya No. 3, Depok', '2025-09-12'],
            ['KCM-000017', 'Dewi Anggraini', '081234500022', 'dewi@gmail.com', '1998-08-25', 'Perempuan', 'Jl. Flamboyan No. 6, Jakarta', '2025-11-02'],
            ['KCM-000018', 'Joko Susilo', '081234500033', 'joko@gmail.com', '1970-09-17', 'Laki-laki', 'Jl. Merpati No. 5, Bogor', '2025-06-18'],
            ['KCM-000019', 'Nadia Kusuma', '081234500044', 'nadia@gmail.com', '1996-05-02', 'Perempuan', 'Jl. Melati Indah No. 3, Bekasi', '2026-02-20'],
            ['KCM-000020', 'Bambang Sutopo', '081234500055', 'bambang@gmail.com', '1968-10-11', 'Laki-laki', 'Jl. Dewi Sartika No. 88, Ciputat', '2024-03-05'],
            ['KCM-000021', 'Rina Marlina', '081234500066', 'rina@gmail.com', '1990-06-08', 'Perempuan', 'Jl. Sukamaju No. 4, Depok', '2025-12-27'],
            ['KCM-000022', 'Ahmad Fauzi', '081234500077', 'ahmad@gmail.com', '1985-12-01', 'Laki-laki', 'Jl. Cendana No. 9, Bekasi', '2026-04-14'],
            ['KCM-000023', 'Maya Lestari', '081234500088', 'maya.lestari@gmail.com', '1995-11-30', 'Perempuan', 'Jl. Dahlia No. 21, Tangerang', '2026-06-09'],
        ];

        return collect($rows)->map(fn (array $row) => Customer::create([
            'member_id' => $row[0],
            'name' => $row[1],
            'phone' => $row[2],
            'email' => $row[3],
            'birth_date' => $row[4],
            'gender' => $row[5],
            'address' => $row[6],
            'registered_at' => $row[7],
            'status' => 'active',
        ]));
    }

    private function seedAdditionalPrescriptions(): void
    {
        Prescription::create([
            'customer_id' => $budiId = Customer::where('member_id', 'KCM-000012')->value('id'),
            'doctor_or_optician' => 'Optometris Dian',
            'examination_date' => '2025-01-18',
            'prescription_type' => 'Distance',
            'od_sph' => '-1.00', 'od_cyl' => '-0.25', 'od_axis' => '175', 'od_add' => '0', 'od_pd' => '31.5',
            'os_sph' => '-0.75', 'os_cyl' => '-0.50', 'os_axis' => '5', 'os_add' => '0', 'os_pd' => '31.5',
            'pd_total' => '63', 'fitting_height' => '18',
            'notes' => 'Resep lama, diganti pada pemeriksaan Agustus 2026.',
        ]);

        $seed = [
            ['KCM-000016', 'Dr. Robert Sp.M', '2026-07-12', 'Distance', '-3.50', '-1.00', '160', '-3.25', '-1.25', '20', '66', '20'],
            ['KCM-000018', 'Optometris Dian', '2026-05-03', 'Reading', '+1.75', '-0.50', '30', '+2.00', '-0.25', '150', '68', '21'],
            ['KCM-000020', 'Dr. Robert Sp.M', '2024-08-09', 'Progressive', '-2.25', '-0.75', '90', '-2.00', '-0.50', '80', '64', '19'],
            ['KCM-000022', 'Optometris Maya', '2026-06-21', 'Distance', '-4.25', '-1.50', '15', '-4.50', '-1.25', '165', '65', '20'],
        ];

        foreach ($seed as $row) {
            $customerId = Customer::where('member_id', $row[0])->value('id');

            Prescription::create([
                'customer_id' => $customerId,
                'doctor_or_optician' => $row[1],
                'examination_date' => $row[2],
                'prescription_type' => $row[3],
                'od_sph' => $row[4], 'od_cyl' => $row[5], 'od_axis' => $row[6], 'od_add' => '+0.75', 'od_pd' => '32',
                'os_sph' => $row[7], 'os_cyl' => $row[8], 'os_axis' => $row[9], 'os_add' => '+0.75', 'os_pd' => '32',
                'pd_total' => $row[10], 'fitting_height' => $row[11],
                'notes' => null,
            ]);
        }

        unset($budiId, $seed);
    }

    private function seedAdditionalPromotions(): void
    {
        Promotion::create([
            'name' => 'Cashback Frame Rp 100.000',
            'description' => 'Cashback nominal Rp 100.000 untuk pembelian frame minimal Rp 750.000.',
            'promo_type' => 'nominal',
            'discount_value' => 100000,
            'min_spend' => 750000,
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);

        Promotion::create([
            'name' => 'Promo Tahun Baru (Berakhir)',
            'description' => 'Promo masa lalu, sudah tidak berlaku.',
            'promo_type' => 'percentage',
            'discount_value' => 20,
            'min_spend' => 0,
            'start_date' => '2025-12-15',
            'end_date' => '2026-01-15',
            'is_active' => false,
        ]);
    }

    /**
     * Riwayat transaksi panjang untuk laporan, grafik tren, dan top produk.
     *
     * @param  Collection<string, Frame>  $frames
     * @param  Collection<string, Lens>  $lenses
     * @param  Collection<int, Customer>  $customers
     */
    private function seedAdditionalTransactions($frames, $lenses, $customers): void
    {
        $stores = Store::orderBy('id')->get();
        $staffMembers = User::whereIn('role', ['admin', 'staff'])->orderBy('id')->get();
        $promos = Promotion::where('is_active', true)->get();
        $allCustomers = Customer::orderBy('id')->get();

        // [daysAgo, status, paymentStatus, promoIndex|null, useFrame]
        $plan = [
            [40, 'completed', 'paid', null, true],
            [37, 'completed', 'paid', 0, false],
            [34, 'completed', 'paid', null, true],
            [31, 'completed', 'paid', null, true],
            [27, 'completed', 'paid', 2, false],
            [25, 'completed', 'paid', null, true],
            [23, 'completed', 'paid', null, false],
            [20, 'completed', 'paid', 1, true],
            [18, 'completed', 'paid', null, true],
            [16, 'completed', 'paid', null, false],
            [13, 'completed', 'paid', 0, true],
            [11, 'completed', 'paid', null, true],
            [9, 'completed', 'paid', null, false],
            [8, 'ready', 'down_payment', 2, true],
            [7, 'processing', 'down_payment', null, false],
            [6, 'ready', 'paid', null, true],
            [5, 'processing', 'unpaid', null, true],
            [4, 'ordered', 'unpaid', null, false],
            [3, 'completed', 'paid', 1, true],
            [2, 'processing', 'unpaid', null, true],
            [1, 'ready', 'down_payment', null, false],
            [0, 'ordered', 'unpaid', null, true],
            [0, 'completed', 'paid', 0, true],
        ];

        $framePool = $frames->values();
        $lensPool = $lenses->values();
        $sequence = 200;

        foreach ($plan as $index => [$daysAgo, $status, $paymentStatus, $promoIndex, $useFrame]) {
            $date = now()->subDays($daysAgo);
            $customer = $allCustomers[$index % $allCustomers->count()];
            $staffMember = $staffMembers[$index % $staffMembers->count()];
            $store = $stores->firstWhere('id', $staffMember->store_id) ?? $stores->first();
            $promo = $promoIndex === null ? null : $promos->values()->get($promoIndex);

            $transaction = Transaction::create([
                'invoice_number' => 'TRX-'.$date->format('Ymd').'-'.str_pad((string) $sequence++, 5, '0', STR_PAD_LEFT),
                'store_id' => $store->id,
                'staff_id' => $staffMember->id,
                'customer_id' => $customer->id,
                'prescription_id' => $customer->prescriptions()->value('id'),
                'subtotal' => 0,
                'discount_amount' => 0,
                'promo_id' => $promo?->id,
                'total_amount' => 0,
                'payment_status' => $paymentStatus,
                'payment_method' => ['cash', 'transfer', 'qris', 'card'][$index % 4],
                'status' => $status,
                'notes' => $index % 6 === 0 ? 'Pelanggan meminta penyesuaian PD.' : null,
                'transaction_date' => $date->setTime(10 + ($index % 8), ($index * 7) % 60),
            ]);

            $subtotal = 0.0;
            $lineCount = $index % 3 === 0 ? 2 : 1;

            $wearer = TransactionUser::create([
                'transaction_id' => $transaction->id,
                'name' => $customer->name,
                'prescription_id' => $transaction->prescription_id,
            ]);

            for ($line = 0; $line < $lineCount; $line++) {
                $isFrame = $line === 0 ? $useFrame : ! $useFrame;

                if ($isFrame) {
                    $product = $framePool[($index + $line) % $framePool->count()];
                    $itemType = 'frame';
                    $frameId = $product->id;
                    $lensId = null;
                } else {
                    $product = $lensPool[($index + $line) % $lensPool->count()];
                    $itemType = 'lens';
                    $frameId = null;
                    $lensId = $product->id;
                }

                $price = (float) $product->sell_price;
                $quantity = $index % 5 === 0 && $line === 1 ? 2 : 1;
                $lineSubtotal = $price * $quantity;
                $subtotal += $lineSubtotal;

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'transaction_user_id' => $wearer->id,
                    'item_type' => $itemType,
                    'frame_id' => $frameId,
                    'lens_id' => $lensId,
                    'name' => $product->name,
                    'quantity' => $quantity,
                    'price' => $price,
                    'discount' => 0,
                    'subtotal' => $lineSubtotal,
                ]);
            }

            $discount = $promo ? $this->promotionDiscount($promo, $subtotal) : 0.0;
            $total = max(0, $subtotal - $discount);

            $transaction->update([
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'total_amount' => $total,
            ]);

            $paid = match ($paymentStatus) {
                'paid' => $total,
                'down_payment' => $total > 0 ? (int) round($total / 2, -3) : 0,
                default => 0.0,
            };

            if ($paid > 0) {
                Payment::create([
                    'transaction_id' => $transaction->id,
                    'amount' => $paid,
                    'payment_method' => $transaction->payment_method,
                    'reference_number' => strtoupper($transaction->payment_method).'-'.$date->format('ymd').'-'.str_pad((string) ($sequence + $index), 4, '0', STR_PAD_LEFT),
                    'paid_at' => $date->copy()->addHours(2),
                    'note' => $paymentStatus === 'down_payment' ? 'DP 50%' : 'Pelunasan penuh.',
                ]);
            }
        }

        unset($plan, $framePool, $lensPool, $promoIndex, $product);
    }

    private function promotionDiscount(Promotion $promotion, float $subtotal): float
    {
        if ($subtotal < (float) $promotion->min_spend) {
            return 0.0;
        }

        $discount = match ($promotion->promo_type) {
            'percentage', 'member_only' => $subtotal * ((float) $promotion->discount_value / 100),
            'nominal' => (float) $promotion->discount_value,
            default => 0.0,
        };

        return min($discount, $subtotal);
    }
}
