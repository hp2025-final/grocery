<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PDF;

class InventoryBalancesController extends Controller
{
    public function index(Request $request)
    {
        // Get date filters, default to 2025-01-01 and current date
        $from = $request->input('from', '2025-01-01');
        $to = $request->input('to', date('Y-m-d'));
        $search = $request->input('search');

        $query = DB::table('inventories')
            ->select([
                'inventories.id',
                'inventories.name',
                'inventories.unit',
                'inventories.opening_qty',
                'inventories.buy_price',
                'inventories.sale_price',
                // Get historical purchases (before from date) for opening balance
                DB::raw('COALESCE((
                    SELECT SUM(pi.quantity)
                    FROM purchase_items pi
                    JOIN purchases p ON pi.purchase_id = p.id
                    WHERE pi.product_id = inventories.id
                    AND p.purchase_date < "' . $from . '"
                ), 0) as historical_in'),
                // Get historical sales (before from date) for opening balance
                DB::raw('COALESCE((
                    SELECT SUM(si.quantity)
                    FROM sale_items si
                    JOIN sales s ON si.sale_id = s.id
                    WHERE si.product_id = inventories.id
                    AND s.sale_date < "' . $from . '"
                ), 0) as historical_out'),
                // Get purchases within period
                DB::raw('COALESCE((
                    SELECT SUM(pi.quantity)
                    FROM purchase_items pi
                    JOIN purchases p ON pi.purchase_id = p.id
                    WHERE pi.product_id = inventories.id
                    AND p.purchase_date >= "' . $from . '"
                    AND p.purchase_date <= "' . $to . '"
                ), 0) as period_in'),
                // Get sales within period
                DB::raw('COALESCE((
                    SELECT SUM(si.quantity)
                    FROM sale_items si
                    JOIN sales s ON si.sale_id = s.id
                    WHERE si.product_id = inventories.id
                    AND s.sale_date >= "' . $from . '"
                    AND s.sale_date <= "' . $to . '"
                ), 0) as period_out')
            ]);

        // Apply search filter if provided
        if ($search) {
            $query->where('inventories.name', 'LIKE', '%' . $search . '%');
        }

        $inventory = $query->get()
            ->map(function ($item) {
                // Calculate real opening balance including historical transactions
                $opening_balance = ($item->opening_qty ?? 0) + $item->historical_in - $item->historical_out;
                
                return (object)[
                    'id' => $item->id,
                    'name' => $item->name,
                    'unit' => $item->unit,
                    'buy_price' => $item->buy_price,
                    'sale_price' => $item->sale_price,
                    'opening_balance' => is_numeric($opening_balance) ? $opening_balance : 0,
                    'period_in' => is_numeric($item->period_in) ? $item->period_in : 0,
                    'period_out' => is_numeric($item->period_out) ? $item->period_out : 0,
                    'closing_balance' => is_numeric($opening_balance) && is_numeric($item->period_in) && is_numeric($item->period_out) ? 
                        ($opening_balance + $item->period_in - $item->period_out) : 0
                ];
            });

        return view('inventory-balances.index', [
            'inventory' => $inventory,
            'from' => $from,
            'to' => $to
        ]);
    }    public function exportPdf(Request $request)
    {
        // Get date filters, default to '2025-01-01' for from_date and current date for to_date
        $from = $request->input('from', '2025-01-01');
        $to = $request->input('to', date('Y-m-d'));
        $search = $request->input('search');

        $query = DB::table('inventories')
            ->select([
                'inventories.id',
                'inventories.name',
                'inventories.unit',
                'inventories.opening_qty',
                'inventories.buy_price',
                'inventories.sale_price',
                // Get historical purchases (before from date) for opening balance
                DB::raw('COALESCE((
                    SELECT SUM(pi.quantity)
                    FROM purchase_items pi
                    JOIN purchases p ON pi.purchase_id = p.id
                    WHERE pi.product_id = inventories.id
                    AND p.purchase_date < "' . $from . '"
                ), 0) as historical_in'),
                // Get historical sales (before from date) for opening balance
                DB::raw('COALESCE((
                    SELECT SUM(si.quantity)
                    FROM sale_items si
                    JOIN sales s ON si.sale_id = s.id
                    WHERE si.product_id = inventories.id
                    AND s.sale_date < "' . $from . '"
                ), 0) as historical_out'),
                // Get purchases within period
                DB::raw('COALESCE((
                    SELECT SUM(pi.quantity)
                    FROM purchase_items pi
                    JOIN purchases p ON pi.purchase_id = p.id
                    WHERE pi.product_id = inventories.id
                    AND p.purchase_date >= "' . $from . '"
                    AND p.purchase_date <= "' . $to . '"
                ), 0) as period_in'),
                // Get sales within period
                DB::raw('COALESCE((
                    SELECT SUM(si.quantity)
                    FROM sale_items si
                    JOIN sales s ON si.sale_id = s.id
                    WHERE si.product_id = inventories.id
                    AND s.sale_date >= "' . $from . '"
                    AND s.sale_date <= "' . $to . '"
                ), 0) as period_out')
            ]);

        // Apply search filter if provided
        if ($search) {
            $query->where('inventories.name', 'LIKE', '%' . $search . '%');
        }

        $inventory = $query->get()
            ->map(function ($item) {
                // Calculate real opening balance including historical transactions
                $opening_balance = ($item->opening_qty ?? 0) + $item->historical_in - $item->historical_out;
                
                return (object)[
                    'id' => $item->id,
                    'name' => $item->name,
                    'unit' => $item->unit,
                    'buy_price' => $item->buy_price,
                    'sale_price' => $item->sale_price,
                    'opening_balance' => is_numeric($opening_balance) ? $opening_balance : 0,
                    'period_in' => is_numeric($item->period_in) ? $item->period_in : 0,
                    'period_out' => is_numeric($item->period_out) ? $item->period_out : 0,
                    'closing_balance' => is_numeric($opening_balance) && is_numeric($item->period_in) && is_numeric($item->period_out) ? 
                        ($opening_balance + $item->period_in - $item->period_out) : 0
                ];
            });

        $pdf = PDF::loadView('pdfs.inventory-balances', [
            'inventory' => $inventory,
            'from' => $from,
            'to' => $to
        ]);

        return $pdf->download('inventory-balances-' . $from . '-to-' . $to . '.pdf');
    }    public function exportPdfWithoutPrices(Request $request)
    {
        // Get date filters, default to '2025-01-01' for from_date and current date for to_date
        $from = $request->input('from', '2025-01-01');
        $to = $request->input('to', date('Y-m-d'));
        $search = $request->input('search');

        $query = DB::table('inventories')
            ->select([
                'inventories.id',
                'inventories.name',
                'inventories.unit',
                'inventories.opening_qty',
                // Get historical purchases (before from date) for opening balance
                DB::raw('COALESCE((
                    SELECT SUM(pi.quantity)
                    FROM purchase_items pi
                    JOIN purchases p ON pi.purchase_id = p.id
                    WHERE pi.product_id = inventories.id
                    AND p.purchase_date < "' . $from . '"
                ), 0) as historical_in'),
                // Get historical sales (before from date) for opening balance
                DB::raw('COALESCE((
                    SELECT SUM(si.quantity)
                    FROM sale_items si
                    JOIN sales s ON si.sale_id = s.id
                    WHERE si.product_id = inventories.id
                    AND s.sale_date < "' . $from . '"
                ), 0) as historical_out'),
                // Get purchases within period
                DB::raw('COALESCE((
                    SELECT SUM(pi.quantity)
                    FROM purchase_items pi
                    JOIN purchases p ON pi.purchase_id = p.id
                    WHERE pi.product_id = inventories.id
                    AND p.purchase_date >= "' . $from . '"
                    AND p.purchase_date <= "' . $to . '"
                ), 0) as period_in'),
                // Get sales within period
                DB::raw('COALESCE((
                    SELECT SUM(si.quantity)
                    FROM sale_items si
                    JOIN sales s ON si.sale_id = s.id
                    WHERE si.product_id = inventories.id
                    AND s.sale_date >= "' . $from . '"
                    AND s.sale_date <= "' . $to . '"
                ), 0) as period_out')
            ]);

        // Apply search filter if provided
        if ($search) {
            $query->where('inventories.name', 'LIKE', '%' . $search . '%');
        }

        $inventory = $query->get()
            ->map(function ($item) {
                // Calculate real opening balance including historical transactions
                $opening_balance = ($item->opening_qty ?? 0) + $item->historical_in - $item->historical_out;
                
                return (object)[
                    'id' => $item->id,
                    'name' => $item->name,
                    'unit' => $item->unit,
                    'opening_balance' => is_numeric($opening_balance) ? $opening_balance : 0,
                    'period_in' => is_numeric($item->period_in) ? $item->period_in : 0,
                    'period_out' => is_numeric($item->period_out) ? $item->period_out : 0,
                    'closing_balance' => is_numeric($opening_balance) && is_numeric($item->period_in) && is_numeric($item->period_out) ? 
                        ($opening_balance + $item->period_in - $item->period_out) : 0
                ];
            });

        $pdf = PDF::loadView('pdfs.inventory-balances-without-prices', [
            'inventory' => $inventory,
            'from' => $from,
            'to' => $to
        ]);

        return $pdf->download('inventory-balances-without-prices-' . $from . '-to-' . $to . '.pdf');
    }
}