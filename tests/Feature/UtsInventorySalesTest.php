<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UtsInventorySalesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private Category $category;
    private Supplier $supplier;
    private Product $productA;
    private Product $productB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Test',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => 'admin123',
            'role' => 'admin',
        ]);

        $this->staff = User::create([
            'name' => 'Staff Test',
            'username' => 'staff',
            'email' => 'staff@example.com',
            'password' => 'staff123',
            'role' => 'staff',
        ]);

        $this->category = Category::create([
            'name' => 'Komponen Komputer',
            'description' => 'Hardware PC',
        ]);

        $this->supplier = Supplier::create([
            'name' => 'PT Supplier Nusantara',
            'phone' => '021-555111',
            'email' => 'supplier@example.com',
        ]);

        $this->productA = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'SKU-TEST-001',
            'name' => 'Keyboard Mechanical',
            'purchase_price' => 400000,
            'selling_price' => 500000,
            'price' => 500000,
            'stock' => 10,
            'minimum_stock' => 5,
        ]);

        $this->productB = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'SKU-TEST-002',
            'name' => 'Mouse Wireless',
            'purchase_price' => 150000,
            'selling_price' => 200000,
            'price' => 200000,
            'stock' => 4,
            'minimum_stock' => 5,
        ]);
    }

    /**
     * 1. Auth Test: Guest tidak dapat membuka dashboard; password salah ditolak.
     */
    public function test_guest_cannot_access_dashboard_and_wrong_password_rejected(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');

        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHas('danger');
        $this->assertGuest();
    }

    /**
     * 2. Product Validation Test: SKU duplikat ditolak; harga negatif ditolak.
     */
    public function test_duplicate_sku_and_negative_price_are_rejected(): void
    {
        $response = $this->actingAs($this->admin)->post('/products', [
            'sku' => 'SKU-TEST-001', // Duplicate SKU
            'name' => 'Produk Duplikat',
            'category_id' => $this->category->id,
            'purchase_price' => -10000, // Negative price
            'selling_price' => -5000,
            'minimum_stock' => 5,
        ]);

        $response->assertSessionHasErrors(['sku', 'purchase_price', 'selling_price']);
    }

    /**
     * 3. Purchase Test: Pembelian sukses menambah stok sesuai kuantitas & menghitung total di server.
     */
    public function test_purchase_transaction_increases_product_stock_and_calculates_total(): void
    {
        $response = $this->actingAs($this->staff)->post('/purchases', [
            'supplier_id' => $this->supplier->id,
            'notes' => 'Restock Gudang',
            'items' => [
                [
                    'product_id' => $this->productA->id,
                    'quantity' => 5,
                    'unit_cost' => 400000,
                ],
                [
                    'product_id' => $this->productB->id,
                    'quantity' => 10,
                    'unit_cost' => 150000,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertEquals(15, $this->productA->fresh()->stock); // 10 + 5
        $this->assertEquals(14, $this->productB->fresh()->stock); // 4 + 10

        $purchase = Purchase::first();
        $this->assertNotNull($purchase);
        $this->assertEquals((5 * 400000) + (10 * 150000), (float) $purchase->total);
    }

    /**
     * 4. Sale Test: Penjualan sukses mengurangi stok sesuai kuantitas & menggunakan harga database.
     */
    public function test_sale_transaction_decreases_product_stock_and_uses_server_price(): void
    {
        $response = $this->actingAs($this->staff)->post('/sales', [
            'customer_name' => 'PT Pelanggan Jaya',
            'items' => [
                [
                    'product_id' => $this->productA->id,
                    'quantity' => 3,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertEquals(7, $this->productA->fresh()->stock); // 10 - 3

        $sale = Sale::first();
        $this->assertNotNull($sale);
        $this->assertEquals(3 * 500000, (float) $sale->total);
    }

    /**
     * 5. Insufficient Stock Test: Penjualan dengan stok tidak cukup ditolak tanpa perubahan data.
     */
    public function test_sale_with_insufficient_stock_is_rejected_without_changing_data(): void
    {
        // Product B stock is 4, customer tries to buy 6
        $response = $this->actingAs($this->staff)->post('/sales', [
            'customer_name' => 'Customer Oversell',
            'items' => [
                [
                    'product_id' => $this->productB->id,
                    'quantity' => 6,
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['items']);
        $this->assertEquals(4, $this->productB->fresh()->stock);
        $this->assertEquals(0, Sale::count());
    }

    /**
     * 6. Atomicity Test: Jika satu item transaksi gagal, seluruh transaksi rollback.
     */
    public function test_atomicity_rollback_when_second_item_fails_stock_check(): void
    {
        // Item 1 (Product A: stock 10, buy 2 -> valid)
        // Item 2 (Product B: stock 4, buy 8 -> invalid/insufficient)
        $response = $this->actingAs($this->staff)->post('/sales', [
            'customer_name' => 'Atomicity Customer',
            'items' => [
                [
                    'product_id' => $this->productA->id,
                    'quantity' => 2,
                ],
                [
                    'product_id' => $this->productB->id,
                    'quantity' => 8,
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['items']);
        // Product A stock MUST remain 10 (rolled back!)
        $this->assertEquals(10, $this->productA->fresh()->stock);
        $this->assertEquals(4, $this->productB->fresh()->stock);
        $this->assertEquals(0, Sale::count());
    }

    /**
     * 7. Authorization Test: Staff tidak dapat mengakses fungsi Admin.
     */
    public function test_staff_cannot_access_admin_routes(): void
    {
        $this->actingAs($this->staff)->get('/users')->assertStatus(403);
        $this->actingAs($this->staff)->post('/categories', ['name' => 'Illegal Cat'])->assertStatus(403);
        $this->actingAs($this->staff)->delete("/products/{$this->productA->id}")->assertStatus(403);
    }

    /**
     * 8. Search & Filter Test: Filter dan pagination menghasilkan data yang sesuai.
     */
    public function test_search_and_low_stock_filter_return_matching_products(): void
    {
        $response = $this->actingAs($this->staff)->get('/products?search=Keyboard');
        $response->assertStatus(200);
        $response->assertSee('Keyboard Mechanical');
        $response->assertDontSee('Mouse Wireless');

        $lowStockResponse = $this->actingAs($this->staff)->get('/products?low_stock=1');
        $lowStockResponse->assertStatus(200);
        $lowStockResponse->assertSee('Mouse Wireless');
    }
}
