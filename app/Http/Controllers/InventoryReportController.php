<?php

namespace App\Http\Controllers;

use App\Models\InventoryCategory;
use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryReportController extends Controller
{    public function exportProductListPdf()
    {
        $categories = InventoryCategory::with(['inventories' => function($query) {
            $query->select('id', 'name', 'unit', 'category_id')
                  ->orderBy('name');
        }])
        ->orderBy('name')
        ->get();        $pdf = \PDF::loadView('pdfs.product-list-by-category', compact('categories'));
        return $pdf->download('product-list-by-category-' . date('Y-m-d') . '.pdf');
    }
      public function productList()
    {
        $categories = InventoryCategory::with(['inventories' => function($query) {
            $query->select('id', 'name', 'unit', 'category_id')
                  ->orderBy('name');
        }])
        ->orderBy('name')
        ->get();

        return view('reports.inventory.product-list', compact('categories'));
    }

    public function byCategory(Request $request)
    {
        $selectedCategory = $request->input('category');
        
        $query = InventoryCategory::with(['inventories' => function($query) {
            $query->orderBy('name')
                  ->withSum('purchaseItems as total_in', 'quantity')
                  ->withSum('saleItems as total_out', 'quantity');
        }])->orderBy('name');

        if ($selectedCategory) {
            $query->where('id', $selectedCategory);
        }

        $categories = $query->get();

        // Calculate totals for each category
        $categories->each(function($category) {
            $category->total_products = $category->inventories->count();
            $category->total_value = $category->inventories->sum(function($product) {
                $opening_qty = $product->opening_qty ?? 0;
                $in_qty = $product->total_in ?? 0;
                $out_qty = $product->total_out ?? 0;
                $current_qty = $opening_qty + $in_qty - $out_qty;
                return $current_qty * $product->sale_price;
            });
        });

        // Get all categories for filter dropdown
        $allCategories = InventoryCategory::orderBy('name')->get();

        return view('reports.inventory.by-category', compact('categories', 'allCategories', 'selectedCategory'));
    }
}
