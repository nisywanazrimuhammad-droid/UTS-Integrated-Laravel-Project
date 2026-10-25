<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Users & Profiles (Supports both username & email login)
        $admin = User::create([
            'name' => 'Administrator Sistem',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => 'admin123',
            'role' => 'admin',
        ]);
        UserProfile::create([
            'user_id' => $admin->id,
            'full_name' => 'Administrator Sistem',
            'phone' => '081234567890',
            'address' => 'Gedung Pusat Operasional Lantai 3',
        ]);

        $staff = User::create([
            'name' => 'Budi Santoso',
            'username' => 'staff',
            'email' => 'staff@example.com',
            'password' => 'staff123',
            'role' => 'staff',
        ]);
        UserProfile::create([
            'user_id' => $staff->id,
            'full_name' => 'Budi Santoso (Staff Gudang & Kasir)',
            'phone' => '082198765432',
            'address' => 'Jl. Pergudangan Logistik Blok B No. 4',
        ]);

        $siti = User::create([
            'name' => 'Siti Rahma',
            'username' => 'siti',
            'email' => 'siti@example.com',
            'password' => 'staff123',
            'role' => 'staff',
        ]);
        UserProfile::create([
            'user_id' => $siti->id,
            'full_name' => 'Siti Rahma (Staff Operasional)',
            'phone' => '085712348765',
            'address' => 'Jl. Melati Raya No. 12',
        ]);

        // 2. Seed Categories
        $cat1 = Category::create(['name' => 'Komponen Komputer', 'description' => 'Perangkat keras internal komputer dan laptop']);
        $cat2 = Category::create(['name' => 'Aksesoris & Periferal', 'description' => 'Perangkat input/output seperti keyboard, mouse, webcam']);
        $cat3 = Category::create(['name' => 'Gadget & Audio', 'description' => 'Perangkat audio, speaker, headphone nirkabel']);
        $cat4 = Category::create(['name' => 'Peralatan Ergonomis', 'description' => 'Kursi dan meja kerja pendukung produktivitas']);
        $cat5 = Category::create(['name' => 'Jaringan & Konektivitas', 'description' => 'Router, switch, adaptor dan kabel jaringan']);

        // 3. Seed Suppliers
        $sup1 = Supplier::create([
            'name' => 'PT Nusantara Tech Logistik',
            'contact_person' => 'Ir. Hendra Gunawan',
            'phone' => '021-5551234',
            'email' => 'sales@nusantaratech.id',
            'address' => 'Kawasan Industri Pulogadung Kav 18, Jakarta'
        ]);
        $sup2 = Supplier::create([
            'name' => 'CV Maju Jaya Elektronik',
            'contact_person' => 'Dewi Lestari',
            'phone' => '022-7778901',
            'email' => 'order@majujaya.co.id',
            'address' => 'Jl. Soekarno Hatta No. 102, Bandung'
        ]);
        $sup3 = Supplier::create([
            'name' => 'Global Peripherals Supply',
            'contact_person' => 'Michael Tan',
            'phone' => '031-8884321',
            'email' => 'support@globalperipherals.com',
            'address' => 'Rungkut Industri III No. 45, Surabaya'
        ]);
        $sup4 = Supplier::create([
            'name' => 'Mitra Ergonomis Indonesia',
            'contact_person' => 'Rian Hidayat',
            'phone' => '021-9993322',
            'email' => 'info@mitraergonomis.id',
            'address' => 'Sentra Niaga Puri Indah Blok C, Jakarta Barat'
        ]);

        // 4. Seed Products (with purchase_price, selling_price, and price)
        $p1 = Product::create(['category_id' => $cat2->id, 'sku' => 'SKU-KB-001', 'name' => 'Mechanical Keyboard RGB TKL Brown Switch', 'purchase_price' => 520000, 'selling_price' => 650000, 'price' => 650000, 'stock' => 28, 'minimum_stock' => 10]);
        $p2 = Product::create(['category_id' => $cat2->id, 'sku' => 'SKU-MS-002', 'name' => 'Wireless Gaming Mouse 16000 DPI Ultra-Light', 'purchase_price' => 295000, 'selling_price' => 380000, 'price' => 380000, 'stock' => 4, 'minimum_stock' => 10]);
        $p3 = Product::create(['category_id' => $cat1->id, 'sku' => 'SKU-MN-003', 'name' => 'Monitor Gaming 24 Inch IPS 144Hz 1ms', 'purchase_price' => 1600000, 'selling_price' => 1950000, 'price' => 1950000, 'stock' => 15, 'minimum_stock' => 5]);
        $p4 = Product::create(['category_id' => $cat3->id, 'sku' => 'SKU-HS-004', 'name' => 'Noise-Cancelling Wireless Headset Bluetooth', 'purchase_price' => 600000, 'selling_price' => 750000, 'price' => 750000, 'stock' => 3, 'minimum_stock' => 8]);
        $p5 = Product::create(['category_id' => $cat2->id, 'sku' => 'SKU-WC-005', 'name' => 'Webcam Full HD 1080p with Dual Microphone', 'purchase_price' => 220000, 'selling_price' => 290000, 'price' => 290000, 'stock' => 35, 'minimum_stock' => 12]);
        $p6 = Product::create(['category_id' => $cat3->id, 'sku' => 'SKU-SP-006', 'name' => 'Portable Bluetooth Speaker Waterproof IPX7', 'purchase_price' => 330000, 'selling_price' => 420000, 'price' => 420000, 'stock' => 0, 'minimum_stock' => 5]);
        $p7 = Product::create(['category_id' => $cat4->id, 'sku' => 'SKU-CH-007', 'name' => 'Kursi Kerja Ergonomis Mesh Backrest Lumbar Support', 'purchase_price' => 1150000, 'selling_price' => 1450000, 'price' => 1450000, 'stock' => 12, 'minimum_stock' => 5]);
        $p8 = Product::create(['category_id' => $cat4->id, 'sku' => 'SKU-DK-008', 'name' => 'Meja Kerja Minimalis Adjustable 120x60cm', 'purchase_price' => 720000, 'selling_price' => 950000, 'price' => 950000, 'stock' => 18, 'minimum_stock' => 5]);
        $p9 = Product::create(['category_id' => $cat5->id, 'sku' => 'SKU-RT-009', 'name' => 'Wi-Fi 6 Dual-Band Gigabit Router AX1800', 'purchase_price' => 460000, 'selling_price' => 580000, 'price' => 580000, 'stock' => 22, 'minimum_stock' => 8]);
        $p10 = Product::create(['category_id' => $cat1->id, 'sku' => 'SKU-SSD-010', 'name' => 'NVMe M.2 SSD 1TB PCIe Gen4 x4 High Speed', 'purchase_price' => 880000, 'selling_price' => 1100000, 'price' => 1100000, 'stock' => 6, 'minimum_stock' => 10]);

        // 5. Seed Many-to-Many product_suppliers
        $p1->suppliers()->attach([$sup1->id => ['supplier_price' => 520000], $sup3->id => ['supplier_price' => 510000]]);
        $p2->suppliers()->attach([$sup3->id => ['supplier_price' => 295000]]);
        $p3->suppliers()->attach([$sup1->id => ['supplier_price' => 1600000], $sup2->id => ['supplier_price' => 1580000]]);
        $p4->suppliers()->attach([$sup2->id => ['supplier_price' => 600000], $sup3->id => ['supplier_price' => 590000]]);
        $p5->suppliers()->attach([$sup1->id => ['supplier_price' => 220000]]);
        $p6->suppliers()->attach([$sup2->id => ['supplier_price' => 330000]]);
        $p7->suppliers()->attach([$sup4->id => ['supplier_price' => 1150000]]);
        $p8->suppliers()->attach([$sup4->id => ['supplier_price' => 720000]]);
        $p9->suppliers()->attach([$sup1->id => ['supplier_price' => 460000]]);
        $p10->suppliers()->attach([$sup1->id => ['supplier_price' => 880000], $sup2->id => ['supplier_price' => 875000]]);

        // 6. Seed Sample Purchases (Header + Multi-Items)
        $po1 = Purchase::create([
            'supplier_id' => $sup1->id,
            'user_id' => $admin->id,
            'purchase_number' => 'PO-' . now()->subDays(5)->format('Ymd') . '-0001',
            'total' => (35 * 520000) + (20 * 1600000),
            'status' => 'completed',
            'purchased_at' => now()->subDays(5),
            'notes' => 'Pengadaan stok awal Keyboard & Monitor batch Q4',
        ]);
        $po1->items()->createMany([
            ['product_id' => $p1->id, 'quantity' => 35, 'unit_cost' => 520000, 'subtotal' => 35 * 520000],
            ['product_id' => $p3->id, 'quantity' => 20, 'unit_cost' => 1600000, 'subtotal' => 20 * 1600000],
        ]);

        $po2 = Purchase::create([
            'supplier_id' => $sup3->id,
            'user_id' => $staff->id,
            'purchase_number' => 'PO-' . now()->subDays(3)->format('Ymd') . '-0002',
            'total' => (10 * 295000) + (10 * 600000) + (40 * 220000),
            'status' => 'completed',
            'purchased_at' => now()->subDays(3),
            'notes' => 'Restock periferal & headset gudang utama',
        ]);
        $po2->items()->createMany([
            ['product_id' => $p2->id, 'quantity' => 10, 'unit_cost' => 295000, 'subtotal' => 10 * 295000],
            ['product_id' => $p4->id, 'quantity' => 10, 'unit_cost' => 600000, 'subtotal' => 10 * 600000],
            ['product_id' => $p5->id, 'quantity' => 40, 'unit_cost' => 220000, 'subtotal' => 40 * 220000],
        ]);

        $po3 = Purchase::create([
            'supplier_id' => $sup4->id,
            'user_id' => $admin->id,
            'purchase_number' => 'PO-' . now()->subDays(2)->format('Ymd') . '-0003',
            'total' => (15 * 1150000) + (20 * 720000),
            'status' => 'completed',
            'purchased_at' => now()->subDays(2),
            'notes' => 'Penerimaan kursi & meja kerja ergonomis',
        ]);
        $po3->items()->createMany([
            ['product_id' => $p7->id, 'quantity' => 15, 'unit_cost' => 1150000, 'subtotal' => 15 * 1150000],
            ['product_id' => $p8->id, 'quantity' => 20, 'unit_cost' => 720000, 'subtotal' => 20 * 720000],
        ]);

        // 7. Seed Sample Sales (Header + Multi-Items)
        $sale1 = Sale::create([
            'user_id' => $staff->id,
            'sale_number' => 'INV-' . now()->subDays(1)->format('Ymd') . '-0001',
            'customer_name' => 'PT Kreatif Digital Nusantara',
            'total' => (7 * 650000) + (5 * 1950000),
            'status' => 'completed',
            'sold_at' => now()->subDays(1),
            'notes' => 'Pesanan B2B perlengkapan studio',
        ]);
        $sale1->items()->createMany([
            ['product_id' => $p1->id, 'quantity' => 7, 'unit_price' => 650000, 'subtotal' => 7 * 650000],
            ['product_id' => $p3->id, 'quantity' => 5, 'unit_price' => 1950000, 'subtotal' => 5 * 1950000],
        ]);

        $sale2 = Sale::create([
            'user_id' => $siti->id,
            'sale_number' => 'INV-' . now()->format('Ymd') . '-0002',
            'customer_name' => 'CV Solusi Media',
            'total' => (6 * 380000) + (5 * 290000) + (3 * 580000),
            'status' => 'completed',
            'sold_at' => now(),
            'notes' => 'Penjualan retail hari ini',
        ]);
        $sale2->items()->createMany([
            ['product_id' => $p2->id, 'quantity' => 6, 'unit_price' => 380000, 'subtotal' => 6 * 380000],
            ['product_id' => $p5->id, 'quantity' => 5, 'unit_price' => 290000, 'subtotal' => 5 * 290000],
            ['product_id' => $p9->id, 'quantity' => 3, 'unit_price' => 580000, 'subtotal' => 3 * 580000],
        ]);

        // 8. Seed Stock Movements
        $movements = [
            [$p1->id, $admin->id, 'IN', 35, $po1->purchase_number, 'Penerimaan stok awal batch 1'],
            [$p1->id, $staff->id, 'OUT', 7, $sale1->sale_number, 'Pengiriman pesanan internal B2B'],
            [$p2->id, $admin->id, 'IN', 10, $po2->purchase_number, 'Stok awal mouse'],
            [$p2->id, $staff->id, 'OUT', 6, $sale2->sale_number, 'Penjualan offline store'],
            [$p3->id, $admin->id, 'IN', 20, $po1->purchase_number, 'Penerimaan monitor dari supplier'],
            [$p3->id, $siti->id, 'OUT', 5, $sale1->sale_number, 'Instalasi lab komputer kantor'],
            [$p4->id, $staff->id, 'IN', 10, $po2->purchase_number, 'Penerimaan headset'],
            [$p4->id, $staff->id, 'OUT', 7, 'ADJ-OUT-001', 'Distribusi divisi call center'],
            [$p5->id, $admin->id, 'IN', 40, $po2->purchase_number, 'Penerimaan webcam Full HD'],
            [$p5->id, $siti->id, 'OUT', 5, $sale2->sale_number, 'Penjualan retail'],
            [$p6->id, $staff->id, 'IN', 15, 'PO-INIT-006', 'Penerimaan batch speaker awal'],
            [$p6->id, $staff->id, 'OUT', 15, 'INV-FLASH-01', 'Penjualan habis promosi flash sale'],
            [$p7->id, $admin->id, 'IN', 15, $po3->purchase_number, 'Penerimaan kursi ergonomis'],
            [$p7->id, $siti->id, 'OUT', 3, 'ADJ-OUT-002', 'Pengadaan ruang meeting'],
            [$p8->id, $admin->id, 'IN', 20, $po3->purchase_number, 'Penerimaan meja kerja'],
            [$p8->id, $staff->id, 'OUT', 2, 'ADJ-OUT-003', 'Kebutuhan workstation baru'],
            [$p9->id, $siti->id, 'IN', 25, 'PO-INIT-009', 'Penerimaan router AX1800'],
            [$p9->id, $siti->id, 'OUT', 3, $sale2->sale_number, 'Upgrade jaringan cabang'],
            [$p10->id, $admin->id, 'IN', 15, 'PO-INIT-010', 'Penerimaan SSD M.2 NVMe'],
            [$p10->id, $staff->id, 'OUT', 9, 'ADJ-OUT-004', 'Perakitan PC server internal'],
        ];

        foreach ($movements as $m) {
            StockMovement::create([
                'product_id' => $m[0],
                'user_id' => $m[1],
                'type' => $m[2],
                'quantity' => $m[3],
                'reference_number' => $m[4],
                'notes' => $m[5],
            ]);
        }
    }
}
