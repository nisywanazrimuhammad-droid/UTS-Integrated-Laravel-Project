<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function inventory(Request $request)
    {
        $categoryId = $request->input('category_id');
        $categories = Category::orderBy('name')->get();

        $query = Product::query()
            ->select([
                'products.id',
                'products.sku',
                'products.name',
                'products.purchase_price',
                'products.selling_price',
                'products.price',
                'products.stock',
                'products.minimum_stock',
                'categories.name as category_name',
                DB::raw("COALESCE(SUM(CASE WHEN stock_movements.type = 'IN' THEN stock_movements.quantity ELSE 0 END), 0) as total_in"),
                DB::raw("COALESCE(SUM(CASE WHEN stock_movements.type = 'OUT' THEN stock_movements.quantity ELSE 0 END), 0) as total_out"),
                DB::raw('(products.stock * products.selling_price) as inventory_value'),
                DB::raw('(products.stock * products.purchase_price) as inventory_cost'),
            ])
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->leftJoin('stock_movements', 'stock_movements.product_id', '=', 'products.id')
            ->groupBy([
                'products.id',
                'products.sku',
                'products.name',
                'products.purchase_price',
                'products.selling_price',
                'products.price',
                'products.stock',
                'products.minimum_stock',
                'categories.name',
            ])
            ->orderBy('categories.name')
            ->orderBy('products.name');

        if (!empty($categoryId)) {
            $query->where('products.category_id', $categoryId);
        }

        $report = $query->get();

        $summary = [
            'total_val' => $report->sum('inventory_value'),
            'total_cost' => $report->sum('inventory_cost'),
            'total_stock' => $report->sum('stock'),
            'total_in' => $report->sum('total_in'),
            'total_out' => $report->sum('total_out'),
            'total_purchases' => Purchase::where('status', 'completed')->sum('total'),
            'total_sales' => Sale::where('status', 'completed')->sum('total'),
        ];

        return view('reports.inventory', compact('report', 'categories', 'categoryId', 'summary'));
    }
}
