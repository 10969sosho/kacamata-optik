# SPEC & REQUIREMENTS DOKUMEN BRIEFING KACAMATA

Target: Toko Kacamata Management & Membership Web App (Laravel 13, SQLite / MySQL ready, Blade, Tailwind CSS / Modern CSS, Lucide / Tabler icons).

## 1. Database & Models
1. `stores`: id, code, name, phone, address, is_active, timestamps
2. `users`: id, store_id (nullable), name, email (nullable), phone, password, role ('admin', 'staff', 'customer'), status, timestamps
3. `customers`: id, user_id (nullable), member_id (format KCM-000001 unik), name, phone, email, birth_date, gender, address, registered_at, status ('active', 'inactive'), timestamps
4. `product_categories`: id, name, slug, type ('lens', 'frame', 'accessory'), is_active, timestamps
5. `frames`: id, sku, barcode, name, brand, model, color, material, size (e.g. 52-18-140), gender, buy_price, sell_price, stock, min_stock, status, photo, description, timestamps
6. `lenses`: id, sku, brand, name, category_id, lens_type (Single Vision, Bifocal, Progressive, Photochromic, Blue Light, Anti Reflective, Polarized), material, index (1.56, 1.61, 1.67, 1.74), coating, buy_price, sell_price, stock, min_stock, supplier, timestamps
7. `prescriptions`: id, customer_id, doctor_or_optician, examination_date, prescription_type,
   - OD (Kanan): od_sph, od_cyl, od_axis, od_add, od_pd
   - OS (Kiri): os_sph, os_cyl, os_axis, os_add, os_pd
   - pd_total, fitting_height, notes, timestamps
8. `promotions`: id, name, description, banner, promo_type ('percentage', 'nominal', 'buy_x_get_y', 'member_only'), discount_value, min_spend, start_date, end_date, is_active, timestamps
9. `transactions`: id, invoice_number (TRX-YYYYMMDD-XXXX), store_id, staff_id, customer_id, prescription_id (nullable),
   - subtotal, discount_amount, promo_id (nullable), total_amount, payment_status ('unpaid', 'down_payment', 'paid'), payment_method ('cash', 'transfer', 'qris', 'card'),
   - status ('draft', 'ordered', 'processing', 'ready', 'completed', 'cancelled', 'refunded'), notes, transaction_date, timestamps
10. `transaction_items`: id, transaction_id, item_type ('frame', 'lens', 'custom'), frame_id (nullable), lens_id (nullable), name, quantity, price, discount, subtotal, timestamps
11. `payments`: id, transaction_id, amount, payment_method, reference_number, paid_at, note, timestamps

## 2. Authentication & Multi-Role
- Login Admin / Staff: via Email atau Phone + Password.
- Login Customer: WhatsApp OTP flow. Di MVP: Form input nomor WhatsApp -> request OTP -> generate 6-digit OTP (tampilkan di UI flash message / demo notice "Demo OTP: 123456" jika provider mock aktif) -> verify OTP -> login langsung ke customer dashboard.
- Middleware / Gate:
  - Admin: akses dashboard pusat, master frame, master lens, kategori, customer, transaksi, promo, laporan & analytics, user & store management.
  - Staff: akses POS / Kasir transaksi baru, daftar customer, buat customer baru, input resep lensa, input frame & lens transaksi, ubah status pesanan (processing -> ready -> completed).
  - Customer: portal khusus mobile-friendly berisi Kartu Membership digital (ID, Barcode/QR, status, total spend), Resep Saya (OD/OS terbaru + riwayat), Riwayat Transaksi & status pengerjaan kacamata, Katalog Promo aktif.

## 3. UI/UX ERP Modern
- Menggunakan Tailwind CSS (via CDN atau Vite build) dengan styling elegan, enterprise slate-900 / indigo-600 / emerald accents.
- Font: Inter / Plus Jakarta Sans (Google Fonts).
- Responsive mobile & tablet layout (drawer sidebar di mobile, fixed header, quick bottom navigation untuk portal customer).
- Icon Tabler / Lucide icons (SVG clean inline atau feather icons).
- POS / Buat Transaksi yang interaktif:
  - Step 1: Cari/Pilih Customer (atau modal cepat bikin customer baru)
  - Step 2: Pilih Frame (katalog filterable dengan stok live)
  - Step 3: Pilih Lensa (tipe, index, coating)
  - Step 4: Pilih Resep tersimpan atau input resep baru (OD/OS lengkap)
  - Step 5: Terapkan Promo (otomatis hitung diskon)
  - Step 6: Checkout, pilih status bayar (Lunas / DP), cetak struk / invoice nota kacamata siap cetak.

## 4. Reports & Analytics
- Dashboard Admin:
  - KPI Cards: Penjualan Hari Ini, Penjualan Bulan Ini, Total Transaksi, Rata-rata Nilai Transaksi (ATV), Customer Baru, Repeat Customer.
  - Low Stock Alerts (Frame / Lensa di bawah min_stock).
  - Top 5 Frame terlaris & Top 5 Lensa terlaris.
  - Grafik tren penjualan mingguan/bulanan.
- Halaman Laporan Penjualan:
  - Filter: rentang tanggal, staff kasir, tipe produk, status bayar.
  - Ringkasan: Total Sales, Total Discount, Net Sales, Average Transaction.
  - Fitur Export CSV / Excel untuk data transaksi.
- Customer Analytics & Retention:
  - Customer list dengan Total Spending, Last Transaction Date, Status (>1 tahun sejak periksa terakhir = "Perlu Reminder Periksa").

## 5. Seeders Lengkap
- Toko dummy: "Kacamata Optik Pusat", "Cabang Mall Selatan".
- Users: Admin (admin@optik.com / password / phone 081234567890), Staff (staff@optik.com / password / phone 081234567891), Customer (081298765432 - Budi Santoso).
- Produk Frames: Ray-Ban Aviator, Oakley Holbrook, Gucci Square, Gentle Monster, dll.
- Produk Lenses: Essilor Crizal Easy Pro 1.56, Hoya BlueControl 1.60, Zeiss DriveSafe 1.67, Polycarbonate Polarized, dll.
- Resep contoh: OD & OS dengan SPH, CYL, AXIS, ADD, PD.
- Transaksi contoh dari berbagai status (Completed, Processing, Ready).
- Promo aktif (Diskon Merdeka 15%, Cashback Frame Rp 100.000).
