<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $lowStock = $request->boolean('low_stock');
        $sort = $request->input('sort', 'name');
        $dir = strtolower($request->input('dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        // Allowlist sorting (UTS & PRD Business Rule)
        $allowedSorts = ['name', 'sku', 'purchase_price', 'selling_price', 'price', 'stock', 'created_at'];
        $sortColumn = in_array($sort, $allowedSorts, true) ? $sort : 'name';

        $query = Product::with(['category', 'suppliers'])
            ->withCount('suppliers');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        if ($lowStock) {
            $query->whereColumn('stock', '<=', 'minimum_stock');
        }

        $products = $query->orderBy($sortColumn, $dir)->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get();

        $filters = [
            'search' => $search,
            'category_id' => $categoryId,
            'low_stock' => $lowStock,
            'sort' => $sortColumn,
            'dir' => $dir,
        ];

        return view('products.index', compact('products', 'categories', 'filters'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('products.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        // Supports both selling_price (UTS Slide 15) and price (Mini-Project 4 compatibility)
        if (!$request->filled('selling_price') && $request->filled('price')) {
            $request->merge(['selling_price' => $request->input('price')]);
        }
        if (!$request->filled('purchase_price') && $request->filled('selling_price')) {
            $request->merge(['purchase_price' => round((float) $request->input('selling_price') * 0.8, 2)]);
        }

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'supplier_ids' => ['nullable', 'array'],
            'supplier_ids.*' => ['exists:suppliers,id'],
            'supplier_prices' => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($validated) {
            $stock = (int) ($validated['stock'] ?? 0);

            $product = Product::create([
                'category_id' => $validated['category_id'],
                'sku' => strtoupper(trim($validated['sku'])),
                'name' => $validated['name'],
                'purchase_price' => $validated['purchase_price'],
                'selling_price' => $validated['selling_price'],
                'price' => $validated['selling_price'],
                'stock' => $stock,
                'minimum_stock' => $validated['minimum_stock'],
            ]);

            // Sync Many-to-many suppliers
            if (!empty($validated['supplier_ids'])) {
                $syncData = [];
                foreach ($validated['supplier_ids'] as $sId) {
                    $price = $validated['supplier_prices'][$sId] ?? $validated['purchase_price'];
                    $syncData[$sId] = ['supplier_price' => !empty($price) ? (float) $price : $validated['purchase_price']];
                }
                $product->suppliers()->sync($syncData);
            }

            // Catat stok awal jika > 0
            if ($stock > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'IN',
                    'quantity' => $stock,
                    'reference_number' => 'INIT-' . $product->sku,
                    'notes' => 'Stok awal saat pendaftaran produk',
                ]);
            }
        });

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'suppliers']);
        $history = $product->stockMovements()->with('user.profile')->latest()->paginate(10);
        $purchaseHistory = $product->purchaseItems()->with(['purchase.supplier', 'purchase.user'])->latest()->limit(5)->get();
        $saleHistory = $product->saleItems()->with(['sale.user'])->latest()->limit(5)->get();

        return view('products.show', compact('product', 'history', 'purchaseHistory', 'saleHistory'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $product->load('suppliers');

        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(Request $request, Product $product)
    {
        if (!$request->filled('selling_price') && $request->filled('price')) {
            $request->merge(['selling_price' => $request->input('price')]);
        }
        if (!$request->filled('purchase_price')) {
            $request->merge(['purchase_price' => $product->purchase_price]);
        }

        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', Rule::unique('products')->ignore($product->id)],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'supplier_ids' => ['nullable', 'array'],
            'supplier_ids.*' => ['exists:suppliers,id'],
            'supplier_prices' => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($product, $validated) {
            $product->update([
                'category_id' => $validated['category_id'],
                'sku' => strtoupper(trim($validated['sku'])),
                'name' => $validated['name'],
                'purchase_price' => $validated['purchase_price'],
                'selling_price' => $validated['selling_price'],
                'price' => $validated['selling_price'],
                'minimum_stock' => $validated['minimum_stock'],
            ]);

            // Sync many-to-many suppliers
            $syncData = [];
            if (!empty($validated['supplier_ids'])) {
                foreach ($validated['supplier_ids'] as $sId) {
                    $price = $validated['supplier_prices'][$sId] ?? $validated['purchase_price'];
                    $syncData[$sId] = ['supplier_price' => !empty($price) ? (float) $price : $validated['purchase_price']];
                }
            }
            $product->suppliers()->sync($syncData);
        });

        return redirect()->route('products.show', $product)
            ->with('success', 'Data produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        // UTS Slide 8: Produk yang sudah dipakai dalam transaksi tidak dihapus secara hard delete
        if ($product->hasTransactionHistory()) {
            return back()->with('danger', "Produk '{$product->name}' tidak dapat dihapus karena sudah memiliki riwayat transaksi pembelian/penjualan/mutasi stok.");
        }

        $product->suppliers()->detach();
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }
}
