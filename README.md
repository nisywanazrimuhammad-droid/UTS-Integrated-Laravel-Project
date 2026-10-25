# 📦 Inventory & Sales Management System (InvenTrack ERP)
**UTS Integrated Laravel Project — Web Programming 2**

Aplikasi manajemen inventaris, pembelian stok (*Stock-In / Purchases*), dan transaksi penjualan (*Sales / Stock-Out*) terintegrasi berbasis **Laravel**, **Eloquent ORM**, dan **MySQL / SQLite**. Proyek ini merupakan integrasi dan pengembangan penuh dari **Mini-Project 3** dan **Mini-Project 4** sesuai spesifikasi penuh **UTS Integrated Laravel Project**.

---

## 1. Project Overview (Deskripsi Proyek)
Sebuah usaha distribusi mengelola katalog produk, kategori, pemasok (*supplier*), transaksi pembelian stok (*purchase order*), dan penjualan (*sales invoice*) melalui beberapa staf. Sistem ini menyelesaikan masalah pencatatan manual dengan memastikan:
- **Konsistensi Stok Otomatis & Atomik**: Stok produk otomatis bertambah saat transaksi pembelian (`Purchases`) disimpan dan otomatis berkurang saat transaksi penjualan (`Sales`) berhasil, dibungkus dalam `DB::transaction()` dan `Product::lockForUpdate()` untuk mencegah *overselling* atau data tersimpan setengah-setengah (*partial write*).
- **Validasi Server-Side & Integritas Harga**: Penjualan yang melebihi stok tersedia langsung ditolak dengan pesan jelas tanpa mengubah data. Subtotal dan total transaksi dihitung murni di sisi server berdasarkan harga resmi di database.
- **Role-Based Access Control (RBAC)**: Pembatasan hak akses di sisi server (Middleware & Laravel Gate) antara **Admin/Owner** dan **Staff**.

---

## 2. System Requirements (Persyaratan Sistem)
- **PHP** >= 8.2 / 8.3
- **Composer** >= 2.x
- **Database**: MySQL 8.0+ / MariaDB (atau SQLite untuk pengujian cepat)
- **Node.js** >= 18.x & NPM (opsional untuk asset bundling)

---

## 3. Installation & Environment Setup (Langkah Instalasi)

Jalankan perintah berikut secara berurutan pada terminal di komputer baru (*clean environment*):

```bash
# 1. Clone repository
git clone https://github.com/nisywanazrimuhammad-droid/Mini-Project-4.git
cd Mini-Project-4

# 2. Install dependensi PHP melalui Composer
composer install

# 3. Salin file environment dan generate application key
cp .env.example .env
php artisan key:generate
```

### Konfigurasi Database di `.env`
**Opsi A — Menggunakan MySQL (Direkomendasikan untuk Demo UTS):**
Buat database kosong bernama `inventory_sales_uts` di phpMyAdmin / MySQL, lalu sesuaikan `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_sales_uts
DB_USERNAME=root
DB_PASSWORD=
```

**Opsi B — Menggunakan SQLite (Tanpa Setup Server Database):**
Secara bawaan `.env.example` mendukung SQLite (`DB_CONNECTION=sqlite`).

---

## 4. Migration & Database Seeding

Jalankan migrasi tabel beserta data awal (*seeder*):

```bash
php artisan migrate --seed
```

Jalankan server aplikasi lokal:

```bash
php artisan serve
```
Buka aplikasi di browser pada alamat: **`http://127.0.0.1:8000`**

---

## 5. Demo Accounts (Akun Demo)

Setelah menjalankan `php artisan migrate --seed`, gunakan akun berikut untuk login (bisa menggunakan **Username** maupun **Email**):

| Role | Username | Email | Password | Hak Akses Utama |
| :--- | :--- | :--- | :--- | :--- |
| **Admin / Owner** | `admin` | `admin@example.com` | `admin123` | Akses penuh: CRUD Kategori, Produk, Supplier, Kelola User, Buat/Batalkan Pembelian & Penjualan, Laporan Agregat |
| **Staff Gudang/Kasir** | `staff` | `staff@example.com` | `staff123` | Akses operasional: Lihat Katalog Produk, Buat Transaksi Pembelian (Stock-In) & Penjualan (Sales), Lihat Laporan |
| **Staff Operasional** | `siti` | `siti@example.com` | `staff123` | Akses operasional Staff |

---

## 6. Entity Relationship Diagram (ERD) & Penjelasan Relasi

Gambar visual ERD tersimpan pada file **`ERD_UTS.png`** di root repositori dan detail kode Draw.io tersedia di **`docs/ERD_Drawio.md`**.

![ERD Inventory & Sales Management System](ERD_UTS.png)

### Ringkasan Relasi Eloquent (8 Tabel Wajib UTS + 3 Tabel Integrasi)
1. **`Category` `hasMany` `Product`** (`products.category_id` $\rightarrow$ `categories.id`, `ON DELETE RESTRICT`)
2. **`Supplier` `hasMany` `Purchase`** (`purchases.supplier_id` $\rightarrow$ `suppliers.id`, `ON DELETE RESTRICT`)
3. **`Purchase` `hasMany` `PurchaseItem`** (`purchase_items.purchase_id` $\rightarrow$ `purchases.id`, `ON DELETE CASCADE`)
4. **`Product` `hasMany` `PurchaseItem`** (`purchase_items.product_id` $\rightarrow$ `products.id`, `ON DELETE RESTRICT`)
5. **`User` `hasMany` `Sale` & `Purchase`** (`sales.user_id` & `purchases.user_id` $\rightarrow$ `users.id`, `ON DELETE RESTRICT`)
6. **`Sale` `hasMany` `SaleItem`** (`sale_items.sale_id` $\rightarrow$ `sales.id`, `ON DELETE CASCADE`)
7. **`Product` `hasMany` `SaleItem`** (`sale_items.product_id` $\rightarrow$ `products.id`, `ON DELETE RESTRICT`)
8. **`User` `hasOne` `UserProfile`** & **`Product` `belongsToMany` `Supplier`** (`product_suppliers`) & **`Product` `hasMany` `StockMovement`** (Integrasi fitur Mini-Project 3 & 4).

---

## 7. Daftar Fitur Utama (Features)

1. **Authentication & Role-Based Authorization**:
   - Login dengan username atau email, password hashing (`bcrypt`), session regeneration, dan proteksi CSRF.
   - Proteksi rute Admin menggunakan `AdminMiddleware` (`middleware('admin')`) dan Laravel Gate (`can:manage-sales`, `can:manage-purchases`).
2. **Master Data (Categories, Products, Suppliers)**:
   - Validasi SKU unik, harga beli (`purchase_price`), harga jual (`selling_price`), stok saat ini (`stock`), dan stok minimum (`minimum_stock`).
   - Proteksi penghapusan: Produk atau kategori yang sudah memiliki riwayat transaksi tidak dapat dihapus sembarangan.
3. **Multi-Item Purchases (Barang Masuk / Stock-In)**:
   - Input banyak item produk sekaligus dalam satu nomor PO (`PO-YYYYMMDD-XXXX`).
   - Stok bertambah otomatis dan harga beli produk diperbarui di dalam `DB::transaction()`.
4. **Multi-Item Sales (Penjualan / Stock-Out)**:
   - Input banyak item produk dalam satu invoice (`INV-YYYYMMDD-XXXX`).
   - Menggunakan `Product::lockForUpdate()` untuk mencegah *race condition / overselling*.
   - Menolak transaksi secara otomatis jika kuantitas melebihi stok yang tersedia dan me-*rollback* seluruh transaksi jika salah satu item gagal.
5. **Pembatalan Transaksi (Void / Cancel Transaction)**:
   - Admin dapat membatalkan transaksi Pembelian atau Penjualan, dan sistem otomatis memulihkan stok produk secara konsisten beserta log auditnya.
6. **Dashboard, Search, Filter & Pagination**:
   - Statistik real-time: Total produk, penjualan hari ini & bulan ini, total pembelian, valuasi aset, dan tabel peringatan *Low Stock*.
   - Pencarian berdasarkan nama/SKU/nomor invoice, filter kategori, filter status, dan filter rentang tanggal dengan `paginate()->withQueryString()`.

---

## 8. Skenario Pengujian (Testing Scenarios)

Seluruh 8 skenario pengujian wajib UTS telah diimplementasikan dalam automated test **`tests/Feature/UtsInventorySalesTest.php`**. Jalankan pengujian dengan perintah:

```bash
php artisan test
```

| No | Skenario Uji | Ekspektasi Hasil | Status |
| :---: | :--- | :--- | :---: |
| 1 | **Auth** | Guest diarahkan ke `/login` saat membuka `/dashboard`; password salah ditolak | ✅ PASS |
| 2 | **Product Validation** | Input SKU duplikat atau harga bernilai negatif ditolak oleh server validation | ✅ PASS |
| 3 | **Purchase (Stock-In)** | Transaksi pembelian multi-item menambah stok masing-masing produk sesuai kuantitas | ✅ PASS |
| 4 | **Sale (Stock-Out)** | Transaksi penjualan multi-item mengurangi stok produk & menghitung total dari harga DB | ✅ PASS |
| 5 | **Insufficient Stock** | Penjualan melebihi stok ditolak dengan pesan error jelas tanpa mengubah data stok | ✅ PASS |
| 6 | **Atomicity (Rollback)** | Jika baris ke-2 pada transaksi multi-item gagal (stok kurang), baris ke-1 ikut di-rollback | ✅ PASS |
| 7 | **Authorization** | Akun `staff` mendapat HTTP `403 Forbidden` saat mengakses rute CRUD Admin (`/users`, dll.) | ✅ PASS |
| 8 | **Search & Filter** | Pencarian SKU/nama, filter kategori, filter stok rendah, dan pagination berjalan akurat | ✅ PASS |

---

## 9. Known Limitations (Batasan Sistem)
- Sistem ini berfokus pada pencatatan stok gudang tunggal (*single warehouse*) dan belum terhubung ke *payment gateway* eksternal sesuai ruang lingkup UTS.
- Pencetakan struk/invoice dan laporan menggunakan fungsi cetak halaman browser (`window.print()`).
