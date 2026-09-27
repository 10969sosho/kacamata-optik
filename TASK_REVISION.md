Langkah revisi Toko Kacamata:

1. Bug NOT NULL constraint failed: prescriptions.prescription_type:
- Buat migration database/migrations/2026_09_27_000001_make_prescription_type_nullable.php untuk modify kolom prescription_type menjadi nullable dengan default('Distance').
- Di app/Models/Prescription.php, beri default attributes: protected $attributes = ['prescription_type' => 'Distance'];
- Di app/Http/Controllers/PosController.php, pastikan $inline['prescription_type'] = !empty($inline['prescription_type']) ? $inline['prescription_type'] : 'Distance';
- Di app/Http/Controllers/PrescriptionController.php, jika prescription_type kosong set ke 'Distance'.
- Jalankan php artisan migrate.

2. Logo Optik:
- Buat file SVG logo modern di public/images/logo.svg (warna merah red-600 dan white/slate, kacamata elegan).
- Pasang logo di:
  * resources/views/layouts/erp.blade.php
  * resources/views/layouts/auth.blade.php
  * resources/views/layouts/member.blade.php
  * resources/views/transactions/print.blade.php

3. Tampilan Tema White & Red:
- Ubah tema sidebar dan header di resources/views/layouts/erp.blade.php menjadi White & Red ERP modern:
  * Sidebar putih bersih (bg-white border-r border-slate-200), menu active dengan pill red (bg-red-50 text-red-600 font-bold border-r-2 border-red-600 atau rounded-xl bg-red-50 text-red-600).
  * Brand badge merah (bg-red-600 text-white).
  * Ganti seluruh warna aksen emerald di layout dan komponen (button utama, status badge, icon highlight, link hover) menjadi warna Red (red-600, hover:bg-red-700, bg-red-50 text-red-600).
- Update tema di resources/views/layouts/auth.blade.php dan resources/views/layouts/member.blade.php ke palet White & Red.

4. Menu POS / Kasir:
- Hapus menu tersendiri 'POS / Kasir' dari sidebar navigasi di resources/views/layouts/erp.blade.php.
- Menu Utama hanya: Dashboard, Transaksi, Customer, Resep (serta Master Produk Frame, Lensa, Kategori, Promo, Report).
- Di routes/web.php, tambahkan route alias:
  Route::get('/transactions/create', [PosController::class, 'create'])->name('transactions.create');
- Di resources/views/transactions/index.blade.php, pastikan tombol '+ Buat Transaksi' mengarah ke form create transaksi.
- Di resources/views/pos/create.blade.php, ubah title menjadi 'Buat Transaksi', subtitle 'Transaksi & pengerjaan kacamata baru'.

5. Testing & Pint:
- Jalankan vendor/bin/pint dan php artisan test, pastikan seluruh 38+ test pass tanpa error.
