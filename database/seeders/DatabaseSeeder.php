<?php

namespace Database\Seeders;

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
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
    }
}
