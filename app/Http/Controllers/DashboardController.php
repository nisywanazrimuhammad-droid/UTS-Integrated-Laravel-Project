<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total produk & Nilai inventory (berdasarkan harga jual & harga beli)
        $totalProducts = Product::count();
        $totalInventoryValue = Product::sum(DB::raw('stock * selling_price'));
        $totalInventoryCost = Product::sum(DB::raw('stock * purchase_price'));

        // 2. Produk stok rendah (stock <= minimum_stock)
        $lowStockCount = Product::whereColumn('stock', '<=', 'minimum_stock')->count();

        // 3. Statistik Penjualan (Sales) - Hari ini, Bulan ini, & Total Keseluruhan (UTS Slide 18)
        $salesToday = Sale::where('status', 'completed')
            ->whereDate('sold_at', today())
            ->sum('total');

        $salesThisMonth = Sale::where('status', 'completed')
            ->whereYear('sold_at', now()->year)
            ->whereMonth('sold_at', now()->month)
            ->sum('total');

        $totalSalesAmount = Sale::where('status', 'completed')->sum('total');
        $totalSalesCount = Sale::where('status', 'completed')->count();

        // 4. Statistik Pembelian (Purchases)
        $totalPurchasesAmount = Purchase::where('status', 'completed')->sum('total');
        $totalPurchasesCount = Purchase::where('status', 'completed')->count();

        // 5. Total mutasi barang masuk (IN) & keluar (OUT)
        $totalIn = StockMovement::where('type', 'IN')->sum('quantity');
        $totalOut = StockMovement::where('type', 'OUT')->sum('quantity');

        // 6. Total supplier & kategori
        $totalCategories = Category::count();
        $totalSuppliers = Supplier::count();

        // 7. Produk dengan stok terendah untuk widget alert
        $lowStockProducts = Product::with('category')
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->orderBy('stock', 'asc')
            ->orderBy('name', 'asc')
            ->limit(6)
            ->get();

        // 8. Transaksi Penjualan & Pembelian Terbaru
        $recentSales = Sale::with(['user.profile'])
            ->withCount('items')
            ->latest('sold_at')
            ->limit(5)
            ->get();

        $recentPurchases = Purchase::with(['supplier', 'user.profile'])
            ->withCount('items')
            ->latest('purchased_at')
            ->limit(5)
            ->get();

        // 9. Riwayat transaksi mutasi terkini
        $recentMovements = StockMovement::with(['product', 'user.profile'])
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            'total_products' => $totalProducts,
            'total_inventory_value' => $totalInventoryValue,
            'total_inventory_cost' => $totalInventoryCost,
            'low_stock_count' => $lowStockCount,
            'sales_today' => $salesToday,
            'sales_this_month' => $salesThisMonth,
            'total_sales_amount' => $totalSalesAmount,
            'total_sales_count' => $totalSalesCount,
            'total_purchases_amount' => $totalPurchasesAmount,
            'total_purchases_count' => $totalPurchasesCount,
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'total_categories' => $totalCategories,
            'total_suppliers' => $totalSuppliers,
            'low_stock_products' => $lowStockProducts,
            'recent_sales' => $recentSales,
            'recent_purchases' => $recentPurchases,
            'recent_movements' => $recentMovements,
        ];

        return view('dashboard.index', compact('stats'));
    }
}
