<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Sale::with(['user.profile'])
            ->withCount('items');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('sale_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        if (!empty($status) && in_array($status, ['completed', 'cancelled'], true)) {
            $query->where('status', $status);
        }

        if (!empty($dateFrom)) {
            $query->whereDate('sold_at', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $query->whereDate('sold_at', '<=', $dateTo);
        }

        $sales = $query->latest('sold_at')->paginate(10)->withQueryString();

        $filters = [
            'search' => $search,
            'status' => $status,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        return view('sales.index', compact('sales', 'filters'));
    }

    public function create(Request $request)
    {
        $products = Product::with('category')->orderBy('name')->get();
        $selectedProductId = $request->input('product_id');

        return view('sales.create', compact('products', 'selectedProductId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:150'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'sold_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'items.required' => 'Minimal harus memilih 1 produk untuk dijual.',
            'items.*.product_id.required' => 'Produk pada setiap baris penjualan wajib dipilih.',
            'items.*.quantity.min' => 'Kuantitas penjualan minimal 1 unit.',
        ]);

        // Implementasi persis sesuai Slide 16 UTS (DB::transaction + lockForUpdate + Server-side calculation)
        $sale = DB::transaction(function () use ($validated) {
            $sale = Sale::create([
                'user_id' => auth()->id(),
                'sale_number' => $this->generateSaleNumber(),
                'customer_name' => $validated['customer_name'] ?: 'Pelanggan Umum / Walk-in',
                'payment_method' => $validated['payment_method'] ?? 'COD (Bayar di Tempat)',
                'total' => 0,
                'status' => 'completed',
                'sold_at' => !empty($validated['sold_at']) ? $validated['sold_at'] : now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            $total = 0;

            foreach ($validated['items'] as $index => $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];

                if ($qty < 1 || $product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok tidak mencukupi untuk produk '{$product->name}' (SKU: {$product->sku}). Stok tersedia: {$product->stock} unit, diminta: {$qty} unit.",
                    ]);
                }

                // Ambil harga jual resmi dari database, bukan dari input browser (UTS Slide 8 & 16)
                $unitPrice = (float) ($product->selling_price > 0 ? $product->selling_price : $product->price);
                $subtotal = $unitPrice * $qty;

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('stock', $qty);

                // Catat juga ke log mutasi stok
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'OUT',
                    'quantity' => $qty,
                    'reference_number' => $sale->sale_number,
                    'notes' => 'Penjualan #' . $sale->sale_number . ($sale->customer_name ? ' (' . $sale->customer_name . ')' : ''),
                ]);

                $total += $subtotal;
            }

            $sale->update(['total' => $total]);

            return $sale;
        });

        return redirect()->route('sales.show', $sale)
            ->with('success', "Transaksi Penjualan #{$sale->sale_number} berhasil disimpan dan stok otomatis berkurang!");
    }

    public function show(Sale $sale)
    {
        $sale->load(['user.profile', 'items.product.category']);

        return view('sales.show', compact('sale'));
    }

    public function cancel(Sale $sale)
    {
        if ($sale->status === 'cancelled') {
            return back()->with('warning', 'Transaksi penjualan ini sudah berstatus dibatalkan.');
        }

        DB::transaction(function () use ($sale) {
            $sale->load('items');

            foreach ($sale->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item->product_id);
                $product->increment('stock', $item->quantity);

                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'IN',
                    'quantity' => $item->quantity,
                    'reference_number' => 'VOID-' . $sale->sale_number,
                    'notes' => "Pembatalan Penjualan #{$sale->sale_number} (Stok Dipulihkan)",
                ]);
            }

            $sale->update(['status' => 'cancelled']);
        });

        return redirect()->route('sales.show', $sale)
            ->with('success', "Transaksi Penjualan #{$sale->sale_number} telah dibatalkan dan stok produk telah dipulihkan.");
    }

    private function generateSaleNumber(): string
    {
        $datePart = now()->format('Ymd');
        $countToday = Sale::whereDate('created_at', today())->count() + 1;
        $number = sprintf('INV-%s-%04d', $datePart, $countToday);

        while (Sale::where('sale_number', $number)->exists()) {
            $countToday++;
            $number = sprintf('INV-%s-%04d', $datePart, $countToday);
        }

        return $number;
    }
}
