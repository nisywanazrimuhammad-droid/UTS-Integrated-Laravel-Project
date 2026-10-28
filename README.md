# 📦 Inventory & Sales Management System (InvenTrack ERP)
**UTS Integrated Laravel Project — Web Programming 2**

Aplikasi manajemen inventaris, pembelian stok (*Stock-In / Purchases*), dan transaksi penjualan (*Sales / Stock-Out*) terintegrasi berbasis **Laravel**, **Eloquent ORM**, dan **MySQL / SQLite**. Proyek ini merupakan integrasi dan pengembangan penuh dari **Mini-Project 3** dan **Mini-Project 4** sesuai spesifikasi penuh **UTS Integrated Laravel Project**.

---

## 1. Project Overview (Deskripsi Proyek)
Sebuah usaha distribusi mengelola katalog produk, kategori, pemasok (*supplier*), transaksi pembelian stok (*purchase order*), dan penjualan (*sales invoice*) melalui beberapa staf. Sistem ini menyelesaikan masalah pencatatan manual dengan memastikan:
- **Konsistensi Stok Otomatis & Atomik**: Stok produk otomatis bertambah saat transaksi pembelian (`Purchases`) disimpan dan otomatis berkurang saat transaksi penjualan (`Sales`) berhasil, dibungkus dalam `DB::transaction()` dan `Product::lockForUpdate()` untuk mencegah *overselling* atau data tersimpan setengah-setengah (*partial write*).
- **Validasi Server-Side & Integritas Harga**: Penjualan yang melebihi stok tersedia langsung ditolak dengan pesan jelas tanpa mengubah data. Subtotal dan total transaksi dihitung murni di sisi server berdasarkan harga resmi di database.
- **Role-Based Access Control (RBAC)**: Pembatasan hak akses di sisi server (Middleware & Laravel Gate) antara **Admin/Owner**, **Staff**, **Customer**

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

## 10. Dokumentasi Sistem Penjualan & Inventaris (Sales & Stock-Out)

Dokumentasi alur dan tampilan antarmuka modul transaksi penjualan pada sistem. Seluruh aset tangkapan layar tersimpan pada direktori `pictures/`.

<img width="723" height="271" alt="Image" src="https://github.com/user-attachments/assets/d0868269-f8e6-45cf-a07c-0e3fbd047a07" />
<img width="718" height="427" alt="Image" src="https://github.com/user-attachments/assets/58c4d3cd-2c85-4f7d-bc0e-b76734fdbfee" />
<img width="742" height="368" alt="Image" src="https://github.com/user-attachments/assets/e8ec4c68-70e7-4952-aba6-de1f12d494d1" />
<img width="713" height="416" alt="Image" src="https://github.com/user-attachments/assets/1a5e84f4-7cc4-41ce-b49c-2d136980b953" />
<img width="720" height="239" alt="Image" src="https://github.com/user-attachments/assets/b807601a-a11d-4595-937b-70d9222abf0a" />
<img width="736" height="429" alt="Image" src="https://github.com/user-attachments/assets/43a808b8-df34-4f69-9a76-699dfb4d1ec7" />
<img width="304" height="260" alt="Image" src="https://github.com/user-attachments/assets/e4f83c14-a79e-44a6-b9a2-4a1c07966da9" />
<img width="353" height="374" alt="Image" src="https://github.com/user-attachments/assets/338934fa-9a0a-4a73-b8cc-7fbf999408d8" />
<img width="731" height="236" alt="Image" src="https://github.com/user-attachments/assets/591fc236-9938-425b-b359-a556d7ead0a8" />
<img width="743" height="425" alt="Image" src="https://github.com/user-attachments/assets/1e645f2e-f089-4426-9cb2-a6e7f3c75102" />
<img width="713" height="421" alt="Image" src="https://github.com/user-attachments/assets/f6291b36-dd7c-4082-a93d-d239be7d0534" />
<img width="731" height="383" alt="Image" src="https://github.com/user-attachments/assets/74cb0e57-9232-40c7-b975-d90a1e883e8e" />
<img width="738" height="437" alt="Image" src="https://github.com/user-attachments/assets/b278c554-b6d9-4754-9a15-376f94df5b4a" />
<img width="317" height="362" alt="Image" src="https://github.com/user-attachments/assets/bd5bad70-97b8-412b-acd3-625cdc0b2586" />
<img width="299" height="366" alt="Image" src="https://github.com/user-attachments/assets/f7692064-965d-4569-aa23-6152ff75f210" />
<img width="728" height="340" alt="Image" src="https://github.com/user-attachments/assets/2e1c1381-17ca-4631-a85c-292ffea19d82" />
<img width="736" height="408" alt="Image" src="https://github.com/user-attachments/assets/86130af3-3ec1-40ac-a13e-9cdfd44f6b8f" />
<img width="749" height="367" alt="Image" src="https://github.com/user-attachments/assets/8ac6ca86-400b-47b3-8fe3-ee6649fedecf" />
<img width="737" height="416" alt="Image" src="https://github.com/user-attachments/assets/96a8ac24-b996-4988-8af9-6f02b0a47c6f" />
<img width="353" height="211" alt="Image" src="https://github.com/user-attachments/assets/ff739857-1d80-4dd6-9072-82d5523dba74" />
<img width="347" height="370" alt="Image" src="https://github.com/user-attachments/assets/32123868-c4b8-4cb5-a646-eead62e011d1" />
<img width="348" height="310" alt="Image" src="https://github.com/user-attachments/assets/e783c810-d26b-470b-8450-8c61ebc0e41b" />
<img width="365" height="383" alt="Image" src="https://github.com/user-attachments/assets/51ffdcf7-7c6c-4678-8c08-680344e0e471" />
<img width="710" height="387" alt="Image" src="https://github.com/user-attachments/assets/a87931f4-fdb5-402c-83e5-73c6ec5a7624" />
<img width="713" height="373" alt="Image" src="https://github.com/user-attachments/assets/249e4f60-cfc9-4e3f-9918-c583877f29ec" />
<img width="698" height="377" alt="Image" src="https://github.com/user-attachments/assets/e58207ed-a950-4a12-ab0a-31d5a862da73" />
<img width="745" height="433" alt="Image" src="https://github.com/user-attachments/assets/1415a221-7e8c-4d34-b6c8-e8b57e25bff8" />
<img width="708" height="230" alt="Image" src="https://github.com/user-attachments/assets/cde8b3f1-975a-4687-bf5a-beb37213e867" />
<img width="722" height="430" alt="Image" src="https://github.com/user-attachments/assets/5b1e299e-90b1-426e-94c1-043b41533b01" />
