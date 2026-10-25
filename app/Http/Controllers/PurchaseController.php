<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $supplierId = $request->input('supplier_id');
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = Purchase::with(['supplier', 'user.profile'])
            ->withCount('items');

        if (!empty($search)) {
            $query->where('purchase_number', 'like', "%{$search}%");
        }

        if (!empty($supplierId)) {
            $query->where('supplier_id', $supplierId);
        }

        if (!empty($status) && in_array($status, ['completed', 'cancelled'], true)) {
            $query->where('status', $status);
        }

        if (!empty($dateFrom)) {
            $query->whereDate('purchased_at', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $query->whereDate('purchased_at', '<=', $dateTo);
        }

        $purchases = $query->latest('purchased_at')->paginate(10)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();

        $filters = [
            'search' => $search,
            'supplier_id' => $supplierId,
            'status' => $status,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        return view('purchases.index', compact('purchases', 'suppliers', 'filters'));
    }

    public function create(Request $request)
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::with('category')->orderBy('name')->get();
        $selectedProductId = $request->input('product_id');
        $selectedSupplierId = $request->input('supplier_id');

        return view('purchases.create', compact('suppliers', 'products', 'selectedProductId', 'selectedSupplierId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchased_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
        ], [
            'supplier_id.required' => 'Silakan pilih pemasok (supplier) terlebih dahulu.',
            'items.required' => 'Minimal harus ada 1 item produk dalam transaksi pembelian.',
            'items.*.product_id.required' => 'Produk pada setiap baris wajib dipilih.',
            'items.*.quantity.min' => 'Kuantitas pembelian minimal 1 unit.',
        ]);

        $purchase = DB::transaction(function () use ($validated) {
            $purchase = Purchase::create([
                'supplier_id' => $validated['supplier_id'],
                'user_id' => auth()->id(),
                'purchase_number' => $this->generatePurchaseNumber(),
                'total' => 0,
                'status' => 'completed',
                'purchased_at' => !empty($validated['purchased_at']) ? $validated['purchased_at'] : now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            $total = 0;

            foreach ($validated['items'] as $index => $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];

                if ($qty < 1) {
                    throw ValidationException::withMessages([
                        "items.{$index}.quantity" => "Kuantitas untuk produk {$product->name} harus lebih dari 0.",
                    ]);
                }

                // Ambil harga beli dari input jika diisi valid, atau fallback ke purchase_price di database
                $unitCost = isset($item['unit_cost']) && $item['unit_cost'] !== '' && $item['unit_cost'] !== null
                    ? (float) $item['unit_cost']
                    : (float) $product->purchase_price;

                if ($unitCost < 0) {
                    throw ValidationException::withMessages([
                        "items.{$index}.unit_cost" => "Harga beli tidak boleh bernilai negatif.",
                    ]);
                }

                // Server-side subtotal calculation (UTS Slide 8)
                $subtotal = $unitCost * $qty;

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);

                // Update harga beli terbaru & tambah stok produk
                $product->increment('stock', $qty);
                if ($unitCost > 0 && (float) $product->purchase_price !== $unitCost) {
                    $product->update(['purchase_price' => $unitCost]);
                }

                // Catat juga pada StockMovement untuk audit trail terpadu
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'type' => 'IN',
                    'quantity' => $qty,
                    'reference_number' => $purchase->purchase_number,
                    'notes' => 'Pembelian #' . $purchase->purchase_number . ($validated['notes'] ? ' - ' . $validated['notes'] : ''),
                ]);

                $total += $subtotal;
            }

            $purchase->update(['total' => $total]);

            return $purchase;
        });

        return redirect()->route('purchases.show', $purchase)
            ->with('success', "Transaksi Pembelian #{$purchase->purchase_number} berhasil disimpan dan stok telah diperbarui!");
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'user.profile', 'items.product.category']);

        return view('purchases.show', compact('purchase'));
    }

    public function cancel(Purchase $purchase)
    {
        if ($purchase->status === 'cancelled') {
            return back()->with('warning', 'Transaksi pembelian ini sudah berstatus dibatalkan.');
        }

        try {
            DB::transaction(function () use ($purchase) {
                $purchase->load('items');

                foreach ($purchase->items as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item->product_id);

                    // Pastikan stok cukup untuk dikurangi saat pembatalan pembelian
                    if ($product->stock < $item->quantity) {
                        throw ValidationException::withMessages([
                            'cancel' => "Tidak dapat membatalkan pembelian karena stok produk '{$product->name}' saat ini ({$product->stock}) sudah terpakai dan lebih kecil dari jumlah pembelian ({$item->quantity}).",
                        ]);
                    }

                    $product->decrement('stock', $item->quantity);

                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => auth()->id(),
                        'type' => 'OUT',
                        'quantity' => $item->quantity,
                        'reference_number' => 'CANCEL-' . $purchase->purchase_number,
                        'notes' => "Pembatalan Pembelian #{$purchase->purchase_number}",
                    ]);
                }

                $purchase->update(['status' => 'cancelled']);
            });

            return redirect()->route('purchases.show', $purchase)
                ->with('success', "Transaksi Pembelian #{$purchase->purchase_number} berhasil dibatalkan dan stok telah dikembalikan.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->with('danger', collect($e->errors())->flatten()->first());
        }
    }

    private function generatePurchaseNumber(): string
    {
        $datePart = now()->format('Ymd');
        $countToday = Purchase::whereDate('created_at', today())->count() + 1;
        $number = sprintf('PO-%s-%04d', $datePart, $countToday);

        while (Purchase::where('purchase_number', $number)->exists()) {
            $countToday++;
            $number = sprintf('PO-%s-%04d', $datePart, $countToday);
        }

        return $number;
    }
}
