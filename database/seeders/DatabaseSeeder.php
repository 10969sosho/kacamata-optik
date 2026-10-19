<?php

namespace Database\Seeders;

use App\Models\Accessory;
use App\Models\Customer;
use App\Models\Frame;
use App\Models\Lens;
use App\Models\Payment;
use App\Models\ProductCategory;
use App\Models\Promotion;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\TransactionPrescription;
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
            'sales_no' => 'TRX-20260920-00123',
            'sales_date' => '2026-09-20',
            'sales_time' => '14:30:00',
            'customer_id' => $budi->id,
            'input_by' => $staff->name,
            'payment_by' => 'transfer',
            'grandtotal' => 3400000,
            'sales_notes' => 'Diskon Merdeka 15% otomatis diterapkan.',
            'sales_status' => 'completed',
        ]);

        $this->seedItem($trx1, 1, 'frame', $frames['FR-RB-001'], $budi->name, 1, 15);
        $this->seedItem($trx1, 2, 'lens', $lenses['LS-ES-001'], $budi->name, 1, 15);

        $this->addResep($trx1, [
            'sortno' => 2,
            'item_type' => 'lens',
            'itemno' => $lenses['LS-ES-001']->id,
            'resep' => 'Dr. Robert Sp.M',
            'jenis' => 'Distance',
            'ro1' => 'Optometris Sari',
            'ro2' => 'Teknisi Rudi',
            'od' => ['sph' => '-1.25', 'cyl' => '-0.50', 'axis' => '90', 'add' => '0', 'pd' => '31.5'],
            'os' => ['sph' => '-1.00', 'cyl' => '-0.25', 'axis' => '80', 'add' => '0', 'pd' => '31.5'],
        ]);

        Payment::create([
            'sales_no' => $trx1->sales_no,
            'amount' => 3400000,
            'payment_method' => 'transfer',
            'reference_number' => 'TRF-20260920-8891',
            'paid_at' => '2026-09-20 14:35:00',
            'note' => 'Pelunasan penuh via transfer bank.',
        ]);

        $trx2 = Transaction::create([
            'sales_no' => 'TRX-20260925-00145',
            'sales_date' => '2026-09-25',
            'sales_time' => '11:00:00',
            'customer_id' => $siti->id,
            'input_by' => $staff->name,
            'payment_by' => 'qris',
            'grandtotal' => 8452360,
            'sales_notes' => 'Potongan Frame Rp 150.000, DP Rp 5.000.000 via QRIS.',
            'sales_status' => 'processing',
        ]);

        $this->seedItem($trx2, 1, 'frame', $frames['FR-GM-001'], $siti->name, 1, 1.74);
        $this->seedItem($trx2, 2, 'lens', $lenses['LS-ZS-001'], $siti->name, 1, 1.74);

        $this->addResep($trx2, [
            'sortno' => 2,
            'item_type' => 'lens',
            'itemno' => $lenses['LS-ZS-001']->id,
            'resep' => 'Optometris Dian',
            'jenis' => 'Progressive',
            'ro1' => 'Optometris Sari',
            'ro2' => 'Teknisi Rudi',
            'od' => ['sph' => '-2.50', 'cyl' => '-1.00', 'axis' => '180', 'add' => '+1.50', 'pd' => '32'],
            'os' => ['sph' => '-2.75', 'cyl' => '-0.75', 'axis' => '175', 'add' => '+1.50', 'pd' => '31'],
            'ket' => 'Butuh lensa progresif untuk jarak jauh dan dekat.',
        ]);

        Payment::create([
            'sales_no' => $trx2->sales_no,
            'amount' => 5000000,
            'payment_method' => 'qris',
            'reference_number' => 'QRIS-20260925-4471',
            'paid_at' => '2026-09-25 11:05:00',
            'note' => 'Uang muka (DP) Rp 5.000.000.',
        ]);

        $trx3 = Transaction::create([
            'sales_no' => 'TRX-20260926-00150',
            'sales_date' => '2026-09-26',
            'sales_time' => '10:15:00',
            'customer_id' => $hendra->id,
            'input_by' => $staff->name,
            'payment_by' => 'cash',
            'grandtotal' => 2900000,
            'sales_notes' => 'Kacamata siap diambil di toko pusat.',
            'sales_status' => 'ready',
        ]);

        $this->seedItem($trx3, 1, 'frame', $frames['FR-OK-001'], $hendra->name, 1, 0);
        $this->seedItem($trx3, 2, 'lens', $lenses['LS-HY-001'], $hendra->name, 1, 0);

        Payment::create([
            'sales_no' => $trx3->sales_no,
            'amount' => 2900000,
            'payment_method' => 'cash',
            'paid_at' => '2026-09-26 10:20:00',
            'note' => 'Pelunasan tunai di kasir.',
        ]);

        $this->seedAdditionalCatalog($frames, $lenses);
        $extraCustomers = $this->seedAdditionalCustomers();
        $this->seedAdditionalPromotions();
        $this->seedAdditionalTransactions($frames, $lenses, $extraCustomers);

        $this->seedMultiWearerTransaction($staff, $budi, $frames, $lenses);
    }

    /**
     * Satu baris item transaksi (stok sengaja tidak dikurangi saat seeding).
     *
     * @param  Frame|Lens|Accessory  $product
     */
    private function seedItem(Transaction $transaction, int $sortno, string $type, $product, string $username, int $qty, float $discPercent): void
    {
        $price = (float) $product->sell_price;

        TransactionItem::create([
            'sales_no' => $transaction->sales_no,
            'sortno' => $sortno,
            'itemno' => (string) $product->id,
            'item_type' => $type,
            'item_description' => $product->name,
            'username' => $username,
            'qty' => $qty,
            'unit' => 'pcs',
            'price' => $price,
            'disc_percent' => $discPercent,
            'subtotal' => round($price * $qty * (1 - $discPercent / 100), 2),
        ]);
    }

    /**
     * Dua baris resep (R = OD kanan, L = OS kiri) menempel pada sortno item.
     *
     * @param  array<string, mixed>  $rx
     */
    private function addResep(Transaction $transaction, array $rx): void
    {
        foreach (['R' => 'od', 'L' => 'os'] as $sisi => $eye) {
            $values = $rx[$eye] ?? [];

            TransactionPrescription::create([
                'sales_no' => $transaction->sales_no,
                'sortno' => $rx['sortno'],
                'itemno_lensa' => $rx['itemno'] ?? null,
                'item_type' => $rx['item_type'],
                'qty' => 1,
                'sisi' => $sisi,
                'sph' => $values['sph'] ?? null,
                'cyl' => $values['cyl'] ?? null,
                'axis' => $values['axis'] ?? null,
                'add' => $values['add'] ?? null,
                'pd' => $values['pd'] ?? null,
                'ro1' => $rx['ro1'] ?? null,
                'ro2' => $rx['ro2'] ?? null,
                'resep' => $rx['resep'] ?? null,
                'jenis_lensa' => $rx['jenis'] ?? null,
                'ket' => $rx['ket'] ?? null,
            ]);
        }
    }

    /**
     * Contoh 1 transaksi / 1 member id dengan beberapa pemakai
     * (Ayah, Anak 1, Anak 2) — masing-masing item & resep sendiri.
     *
     * @param  Collection<string, Frame>  $frames
     * @param  Collection<string, Lens>  $lenses
     */
    private function seedMultiWearerTransaction(User $staff, Customer $customer, $frames, $lenses): void
    {
        $plan = [
            [
                'name' => $customer->name,
                'items' => [['frame', 'FR-RB-002', 1], ['lens', 'LS-ES-002', 1]],
                'resep' => [
                    'resep' => 'Dr. Robert Sp.M',
                    'jenis' => 'Distance',
                    'od' => ['sph' => '-1.25', 'cyl' => '-0.50', 'axis' => '90', 'pd' => '31.5'],
                    'os' => ['sph' => '-1.00', 'cyl' => '-0.25', 'axis' => '80', 'pd' => '31.5'],
                ],
            ],
            [
                'name' => 'Raka (Anak 1)',
                'items' => [['frame', 'FR-KD-001', 1], ['lens', 'LS-HY-001', 1]],
                'resep' => [
                    'resep' => 'Optometris Dian',
                    'od' => ['sph' => '-2.50', 'cyl' => '-0.75', 'axis' => '180', 'pd' => '29'],
                    'os' => ['sph' => '-2.25', 'cyl' => '-0.50', 'axis' => '175', 'pd' => '29'],
                    'ket' => 'Resep anak pertama.',
                ],
            ],
            [
                'name' => 'Rini (Anak 2)',
                'items' => [['lens', 'LS-ES-001', 1]],
                'resep' => [
                    'resep' => 'Optometris Dian',
                    'od' => ['sph' => '-1.75', 'pd' => '28'],
                    'os' => ['sph' => '-1.50', 'pd' => '28'],
                    'ket' => 'Resep anak kedua.',
                ],
            ],
        ];

        $sortno = 0;
        $grandtotal = 0.0;
        $lines = [];

        foreach ($plan as $group) {
            foreach ($group['items'] as [$type, $sku, $qty]) {
                $product = $type === 'frame'
                    ? Frame::query()->where('sku', $sku)->first()
                    : Lens::query()->where('sku', $sku)->first();

                if (! $product) {
                    continue;
                }

                $price = (float) $product->sell_price;
                $grandtotal += $price * $qty;
                $lines[] = ['group' => $group, 'type' => $type, 'product' => $product, 'qty' => $qty, 'price' => $price, 'sortno' => ++$sortno];
            }
        }

        if ($grandtotal <= 0) {
            return;
        }

        $transaction = Transaction::create([
            'sales_no' => 'TRX-'.now()->format('Ymd').'-00200',
            'sales_date' => now()->subDay()->toDateString(),
            'sales_time' => '09:30:00',
            'customer_id' => $customer->id,
            'input_by' => $staff->name,
            'payment_by' => 'cash',
            'grandtotal' => $grandtotal,
            'sales_notes' => 'Satu transaksi untuk ayah & dua anaknya.',
            'sales_status' => 'ordered',
        ]);

        foreach ($lines as $line) {
            $group = $line['group'];

            TransactionItem::create([
                'sales_no' => $transaction->sales_no,
                'sortno' => $line['sortno'],
                'itemno' => (string) $line['product']->id,
                'item_type' => $line['type'],
                'item_description' => $line['product']->name,
                'username' => $group['name'],
                'qty' => $line['qty'],
                'unit' => 'pcs',
                'price' => $line['price'],
                'disc_percent' => 0,
                'subtotal' => $line['price'] * $line['qty'],
            ]);

            if ($line['type'] === 'lens' && isset($group['resep'])) {
                $this->addResep($transaction, $group['resep'] + [
                    'sortno' => $line['sortno'],
                    'item_type' => 'lens',
                    'itemno' => $line['product']->id,
                    'ro1' => 'Optometris Dian',
                    'ro2' => 'Teknisi Rudi',
                ]);
            }
        }

        Payment::create([
            'sales_no' => $transaction->sales_no,
            'amount' => (int) round($grandtotal / 2, -3),
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
        $staffMembers = User::whereIn('role', ['admin', 'staff'])->orderBy('id')->get();
        $promos = Promotion::where('is_active', true)->get();
        $allCustomers = Customer::orderBy('id')->get();

        $reseps = [
            'KCM-000016' => [
                'resep' => 'Dr. Robert Sp.M', 'jenis' => 'Distance',
                'od' => ['sph' => '-3.50', 'cyl' => '-1.00', 'axis' => '160', 'add' => '+0.75', 'pd' => '32'],
                'os' => ['sph' => '-3.25', 'cyl' => '-1.25', 'axis' => '20', 'add' => '+0.75', 'pd' => '32'],
            ],
            'KCM-000018' => [
                'resep' => 'Optometris Dian', 'jenis' => 'Reading',
                'od' => ['sph' => '+1.75', 'cyl' => '-0.50', 'axis' => '30', 'add' => '+0.75', 'pd' => '32'],
                'os' => ['sph' => '+2.00', 'cyl' => '-0.25', 'axis' => '150', 'add' => '+0.75', 'pd' => '32'],
            ],
            'KCM-000020' => [
                'resep' => 'Dr. Robert Sp.M', 'jenis' => 'Progressive',
                'od' => ['sph' => '-2.25', 'cyl' => '-0.75', 'axis' => '90', 'add' => '+0.75', 'pd' => '32'],
                'os' => ['sph' => '-2.00', 'cyl' => '-0.50', 'axis' => '80', 'add' => '+0.75', 'pd' => '32'],
            ],
            'KCM-000022' => [
                'resep' => 'Optometris Maya', 'jenis' => 'Distance',
                'od' => ['sph' => '-4.25', 'cyl' => '-1.50', 'axis' => '15', 'add' => '+0.75', 'pd' => '32'],
                'os' => ['sph' => '-4.50', 'cyl' => '-1.25', 'axis' => '165', 'add' => '+0.75', 'pd' => '32'],
            ],
        ];

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
            $promo = $promoIndex === null ? null : $promos->values()->get($promoIndex);
            $paymentBy = ['cash', 'transfer', 'qris', 'card'][$index % 4];

            $transaction = Transaction::create([
                'sales_no' => 'TRX-'.$date->format('Ymd').'-'.str_pad((string) $sequence++, 5, '0', STR_PAD_LEFT),
                'sales_date' => $date->toDateString(),
                'sales_time' => sprintf('%02d:%02d:00', 10 + ($index % 8), ($index * 7) % 60),
                'customer_id' => $customer->id,
                'input_by' => $staffMember->name,
                'payment_by' => $paymentBy,
                'grandtotal' => 0,
                'sales_notes' => $index % 6 === 0 ? 'Pelanggan meminta penyesuaian PD.' : null,
                'sales_status' => $status,
            ]);

            $gross = 0.0;
            $lineCount = $index % 3 === 0 ? 2 : 1;
            $lines = [];

            for ($line = 0; $line < $lineCount; $line++) {
                $isFrame = $line === 0 ? $useFrame : ! $useFrame;
                $product = $isFrame
                    ? $framePool[($index + $line) % $framePool->count()]
                    : $lensPool[($index + $line) % $lensPool->count()];
                $qty = $index % 5 === 0 && $line === 1 ? 2 : 1;

                $gross += (float) $product->sell_price * $qty;
                $lines[] = ['product' => $product, 'type' => $isFrame ? 'frame' : 'lens', 'qty' => $qty];
            }

            $discount = $promo ? $this->promotionDiscount($promo, $gross) : 0.0;
            $discPercent = $gross > 0 ? round($discount / $gross * 100, 2) : 0.0;
            $grandtotal = 0.0;
            $resepAdded = false;
            $resep = $reseps[$customer->member_id] ?? null;

            foreach ($lines as $position => $line) {
                $sortno = $position + 1;
                $price = (float) $line['product']->sell_price;
                $subtotal = round($price * $line['qty'] * (1 - $discPercent / 100), 2);
                $grandtotal += $subtotal;

                TransactionItem::create([
                    'sales_no' => $transaction->sales_no,
                    'sortno' => $sortno,
                    'itemno' => (string) $line['product']->id,
                    'item_type' => $line['type'],
                    'item_description' => $line['product']->name,
                    'username' => $customer->name,
                    'qty' => $line['qty'],
                    'unit' => 'pcs',
                    'price' => $price,
                    'disc_percent' => $discPercent,
                    'subtotal' => $subtotal,
                ]);

                if ($line['type'] === 'lens' && $resep && ! $resepAdded) {
                    $this->addResep($transaction, $resep + [
                        'sortno' => $sortno,
                        'item_type' => 'lens',
                        'itemno' => $line['product']->id,
                        'ro1' => 'Optometris Sari',
                        'ro2' => 'Teknisi Rudi',
                    ]);
                    $resepAdded = true;
                }
            }

            $transaction->update(['grandtotal' => round($grandtotal, 2)]);

            $paid = match ($paymentStatus) {
                'paid' => $grandtotal,
                'down_payment' => $grandtotal > 0 ? (int) round($grandtotal / 2, -3) : 0,
                default => 0.0,
            };

            if ($paid > 0) {
                Payment::create([
                    'sales_no' => $transaction->sales_no,
                    'amount' => $paid,
                    'payment_method' => $paymentBy,
                    'reference_number' => strtoupper($paymentBy).'-'.$date->format('ymd').'-'.str_pad((string) ($sequence + $index), 4, '0', STR_PAD_LEFT),
                    'paid_at' => $date->copy()->addHours(2),
                    'note' => $paymentStatus === 'down_payment' ? 'DP 50%' : 'Pelunasan penuh.',
                ]);
            }
        }

        unset($plan, $framePool, $lensPool, $promoIndex, $product, $reseps);
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
