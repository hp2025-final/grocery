<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PDF;

class InventoryValuesController extends Controller
{
    public function index(Request $request)
    {
        // Get date filters, default to current date
        $today = date('Y-m-d');
        $from = $request->input('from', $today);
        $to = $request->input('to', $today);

        $inventory = DB::table('inventories')
            ->select([
                'inventories.id',
                'inventories.name',
                'inventories.unit',
                'inventories.opening_qty',
                'inventories.buy_price',
                'inventories.sale_price',
                // Get purchase average rate
                DB::raw('COALESCE((
                    SELECT SUM(pi.quantity * pi.rate) / SUM(pi.quantity)
                    FROM purchase_items pi
                    JOIN purchases p ON pi.purchase_id = p.id
                    WHERE pi.product_id = inventories.id
                ), 0) as purchase_avg_rate'),
                // Get sales average rate
                DB::raw('COALESCE((
                    SELECT SUM(si.quantity * si.rate) / SUM(si.quantity)
                    FROM sale_items si
                    JOIN sales s ON si.sale_id = s.id
                    WHERE si.product_id = inventories.id
                ), 0) as sales_avg_rate'),
                // Get total purchase quantity for this product
                DB::raw('COALESCE((
                    SELECT SUM(pi.quantity)
                    FROM purchase_items pi
                    JOIN purchases p ON pi.purchase_id = p.id
                    WHERE pi.product_id = inventories.id
                ), 0) as total_purchase_qty'),
                // Get total sales quantity for this product
                DB::raw('COALESCE((
                    SELECT SUM(si.quantity)
                    FROM sale_items si
                    JOIN sales s ON si.sale_id = s.id
                    WHERE si.product_id = inventories.id
                ), 0) as total_sales_qty'),
                // Get historical purchases value (before from date)
                DB::raw('COALESCE((
                    SELECT SUM(pi.quantity * pi.rate)
                    FROM purchase_items pi
                    JOIN purchases p ON pi.purchase_id = p.id
                    WHERE pi.product_id = inventories.id
                    AND p.purchase_date < "' . $from . '"
                ), 0) as historical_purchase_value'),
                // Get historical sales value (before from date)
                DB::raw('COALESCE((
                    SELECT SUM(si.quantity * si.rate)
                    FROM sale_items si
                    JOIN sales s ON si.sale_id = s.id
                    WHERE si.product_id = inventories.id
                    AND s.sale_date < "' . $from . '"
                ), 0) as historical_sale_value'),
                // Get period purchases value
                DB::raw('COALESCE((
                    SELECT SUM(pi.quantity * pi.rate)
                    FROM purchase_items pi
                    JOIN purchases p ON pi.purchase_id = p.id
                    WHERE pi.product_id = inventories.id
                    AND p.purchase_date >= "' . $from . '"
                    AND p.purchase_date <= "' . $to . '"
                ), 0) as period_in_value'),
                // Get period sales value
                DB::raw('COALESCE((
                    SELECT SUM(si.quantity * si.rate)
                    FROM sale_items si
                    JOIN sales s ON si.sale_id = s.id
                    WHERE si.product_id = inventories.id
                    AND s.sale_date >= "' . $from . '"
                    AND s.sale_date <= "' . $to . '"
                ), 0) as period_out_value')
            ])
            ->get()
            ->map(function ($item) {
                // Calculate average buy price:
                // (buy_price from inventories + average purchase rate) / 2
                $avg_buy_price = ($item->buy_price + $item->purchase_avg_rate) / 2;

                // Calculate average sale price:
                // (sale_price from inventories + average sales rate) / 2
                $avg_sale_price = ($item->sale_price + $item->sales_avg_rate) / 2;
                
                // Calculate opening value
                $opening_value = ($item->opening_qty * $item->buy_price) + 
                               $item->historical_purchase_value - 
                               $item->historical_sale_value;

                // Calculate final quantity for closing value
                $final_qty = ($item->opening_qty ?? 0) + $item->total_purchase_qty - $item->total_sales_qty;
                
                // Calculate closing value using final quantity and average buy price
                $closing_value = $final_qty * $avg_buy_price;
                
                return (object)[
                    'id' => $item->id,
                    'name' => $item->name,
                    'unit' => $item->unit,
                    'unit_price' => number_format($avg_buy_price, 2),
                    'sale_price' => number_format($avg_sale_price, 2),
                    'opening_value' => number_format($opening_value, 2),
                    'period_in_value' => number_format($item->period_in_value, 2),
                    'period_out_value' => number_format($item->period_out_value, 2),
                    'closing_value' => number_format($closing_value, 2)
                ];
            });

        return view('inventory-values.index', [
            'inventory' => $inventory,
            'from' => $from,
            'to' => $to
        ]);
    }

    public function exportPdf(Request $request)
    {
        // Get date filters, default to current date
        $today = date('Y-m-d');
        $from = $request->input('from', $today);
        $to = $request->input('to', $today);

        $inventory = DB::table('inventories')
            ->select([
                'inventories.id',
                'inventories.name',
                'inventories.unit',
                'inventories.opening_qty',
                'inventories.buy_price',
                'inventories.sale_price',
                // Get purchase average rate
                DB::raw('COALESCE((
                    SELECT SUM(pi.quantity * pi.rate) / SUM(pi.quantity)
                    FROM purchase_items pi
                    JOIN purchases p ON pi.purchase_id = p.id
                    WHERE pi.product_id = inventories.id
                ), 0) as purchase_avg_rate'),
                // Get sales average rate
                DB::raw('COALESCE((
                    SELECT SUM(si.quantity * si.rate) / SUM(si.quantity)
                    FROM sale_items si
                    JOIN sales s ON si.sale_id = s.id
                    WHERE si.product_id = inventories.id
                ), 0) as sales_avg_rate'),
                // Get total purchase quantity for this product
                DB::raw('COALESCE((
                    SELECT SUM(pi.quantity)
                    FROM purchase_items pi
                    JOIN purchases p ON pi.purchase_id = p.id
                    WHERE pi.product_id = inventories.id
                ), 0) as total_purchase_qty'),
                // Get total sales quantity for this product
                DB::raw('COALESCE((
                    SELECT SUM(si.quantity)
                    FROM sale_items si
                    JOIN sales s ON si.sale_id = s.id
                    WHERE si.product_id = inventories.id
                ), 0) as total_sales_qty'),
                // Get historical purchases value (before from date)
                DB::raw('COALESCE((
                    SELECT SUM(pi.quantity * pi.rate)
                    FROM purchase_items pi
                    JOIN purchases p ON pi.purchase_id = p.id
                    WHERE pi.product_id = inventories.id
                    AND p.purchase_date < "' . $from . '"
                ), 0) as historical_purchase_value'),
                // Get historical sales value (before from date)
                DB::raw('COALESCE((
                    SELECT SUM(si.quantity * si.rate)
                    FROM sale_items si
                    JOIN sales s ON si.sale_id = s.id
                    WHERE si.product_id = inventories.id
                    AND s.sale_date < "' . $from . '"
                ), 0) as historical_sale_value'),
                // Get period purchases value
                DB::raw('COALESCE((
                    SELECT SUM(pi.quantity * pi.rate)
                    FROM purchase_items pi
                    JOIN purchases p ON pi.purchase_id = p.id
                    WHERE pi.product_id = inventories.id
                    AND p.purchase_date >= "' . $from . '"
                    AND p.purchase_date <= "' . $to . '"
                ), 0) as period_in_value'),
                // Get period sales value
                DB::raw('COALESCE((
                    SELECT SUM(si.quantity * si.rate)
                    FROM sale_items si
                    JOIN sales s ON si.sale_id = s.id
                    WHERE si.product_id = inventories.id
                    AND s.sale_date >= "' . $from . '"
                    AND s.sale_date <= "' . $to . '"
                ), 0) as period_out_value')
            ])
            ->get()
            ->map(function ($item) {
                // Calculate average buy price:
                // (buy_price from inventories + average purchase rate) / 2
                $avg_buy_price = ($item->buy_price + $item->purchase_avg_rate) / 2;

                // Calculate average sale price:
                // (sale_price from inventories + average sales rate) / 2
                $avg_sale_price = ($item->sale_price + $item->sales_avg_rate) / 2;
                
                // Calculate opening value
                $opening_value = ($item->opening_qty * $item->buy_price) + 
                               $item->historical_purchase_value - 
                               $item->historical_sale_value;

                // Calculate final quantity for closing value
                $final_qty = ($item->opening_qty ?? 0) + $item->total_purchase_qty - $item->total_sales_qty;
                
                // Calculate closing value using final quantity and average buy price
                $closing_value = $final_qty * $avg_buy_price;
                
                return (object)[
                    'id' => $item->id,
                    'name' => $item->name,
                    'unit' => $item->unit,
                    'unit_price' => number_format($avg_buy_price, 2),
                    'sale_price' => number_format($avg_sale_price, 2),
                    'opening_value' => number_format($opening_value, 2),
                    'period_in_value' => number_format($item->period_in_value, 2),
                    'period_out_value' => number_format($item->period_out_value, 2),
                    'closing_value' => number_format($closing_value, 2)
                ];
            });

        $pdf = PDF::loadView('pdfs.inventory-values', [
            'inventory' => $inventory,
            'from' => $from,
            'to' => $to
        ]);

        return $pdf->download('inventory-values-' . $from . '-to-' . $to . '.pdf');
    }

    public function downloadInfo()
    {
        $file = public_path('docs/inventory-values-info.txt');
        return response()->download($file, 'inventory-values-calculation-guide.txt');
    }
} 