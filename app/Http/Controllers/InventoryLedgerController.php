<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;

class InventoryLedgerController extends Controller
{
    public function show($id)
    {
        $inventory = Inventory::findOrFail($id);

        // Use a single raw SQL to merge opening, purchases, and sales, ordered by date
        $sql = "
            SELECT
                '2025-01-01' AS date,
                'Opening' AS party,
                inventories.opening_qty AS `in`,
                NULL AS `out`,
                inventories.buy_price AS rate
            FROM inventories
            WHERE inventories.id = ?

            UNION ALL

            SELECT
                purchases.purchase_date AS date,
                vendors.name AS party,
                purchase_items.quantity AS `in`,
                NULL AS `out`,
                purchase_items.rate AS rate
            FROM purchase_items
            INNER JOIN purchases ON purchase_items.purchase_id = purchases.id
            INNER JOIN vendors ON purchases.vendor_id = vendors.id
            WHERE purchase_items.product_id = ?

            UNION ALL

            SELECT
                sales.sale_date AS date,
                customers.name AS party,
                NULL AS `in`,
                sale_items.quantity AS `out`,
                sale_items.rate AS rate
            FROM sale_items
            INNER JOIN sales ON sale_items.sale_id = sales.id
            INNER JOIN customers ON sales.customer_id = customers.id
            WHERE sale_items.product_id = ?

            ORDER BY date IS NULL, date ASC
        ";

        // Date filter
        $from = request('from');
        $to = request('to');

        $results = \DB::select($sql, [$inventory->id, $inventory->id, $inventory->id]);

        // Calculate running balance and filter by date
        $balance = 0;
        $rows = [];
        $opening_balance = 0;
        $rows = [];
        $pre_filter_balance = 0;
        foreach ($results as $row) {
            $in = isset($row->in) ? ($row->in ?: 0) : 0;
            $out = isset($row->out) ? ($row->out ?: 0) : 0;
            $row = (array) $row;
            $row_date = $row['date'] ?? null;
            // Calculate pre-filter opening balance
            if ($from && $row_date && $row_date < $from) {
                $pre_filter_balance += $in - $out;
                continue;
            }
            $opening_balance += $in - $out;
            // Date filtering (skip if not in range)
            if ($row_date) {
                if ($from && $row_date < $from) continue;
                if ($to && $row_date > $to) continue;
            }
            $row['balance'] = $opening_balance;
            $rows[] = $row;
        }
        // Insert running opening balance row if filter is applied
        if ($from) {
            array_unshift($rows, [
                'date' => $from,
                'party' => 'Opening (as of ' . $from . ')',
                'in' => null,
                'out' => null,
                'rate' => null,
                'balance' => $pre_filter_balance,
            ]);
            // Recalculate balances for filtered rows
            $running = $pre_filter_balance;
            foreach ($rows as $i => $row) {
                if ($i === 0) continue; // skip the synthetic opening row
                $in = isset($row['in']) ? ($row['in'] ?: 0) : 0;
                $out = isset($row['out']) ? ($row['out'] ?: 0) : 0;
                $running += $in - $out;
                $rows[$i]['balance'] = $running;
            }
        }

        // Pagination (20 per page)
        $page = request()->input('page', 1);
        $perPage = 20;
        $pagedRows = new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($rows, ($page - 1) * $perPage, $perPage),
            count($rows),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('inventory.ledger', [
            'inventory' => $inventory,
            'rows' => $pagedRows,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function exportPdf($id, Request $request)
    {
        $inventory = Inventory::findOrFail($id);
        
        // Date filter
        $from = $request->input('from');
        $to = $request->input('to');
        
        // Use a single raw SQL to merge opening, purchases, and sales, ordered by date
        $sql = "
            SELECT
                '2025-01-01' AS date,
                'Opening' AS party,
                inventories.opening_qty AS `in`,
                NULL AS `out`,
                inventories.buy_price AS rate
            FROM inventories
            WHERE inventories.id = ?

            UNION ALL

            SELECT
                purchases.purchase_date AS date,
                vendors.name AS party,
                purchase_items.quantity AS `in`,
                NULL AS `out`,
                purchase_items.rate AS rate
            FROM purchase_items
            INNER JOIN purchases ON purchase_items.purchase_id = purchases.id
            INNER JOIN vendors ON purchases.vendor_id = vendors.id
            WHERE purchase_items.product_id = ?

            UNION ALL

            SELECT
                sales.sale_date AS date,
                customers.name AS party,
                NULL AS `in`,
                sale_items.quantity AS `out`,
                sale_items.rate AS rate
            FROM sale_items
            INNER JOIN sales ON sale_items.sale_id = sales.id
            INNER JOIN customers ON sales.customer_id = customers.id
            WHERE sale_items.product_id = ?

            ORDER BY date IS NULL, date ASC
        ";

        $results = \DB::select($sql, [$inventory->id, $inventory->id, $inventory->id]);

        // Calculate running balance and filter by date
        $balance = 0;
        $rows = [];
        $opening_balance = 0;
        $pre_filter_balance = 0;
        
        foreach ($results as $row) {
            $in = isset($row->in) ? ($row->in ?: 0) : 0;
            $out = isset($row->out) ? ($row->out ?: 0) : 0;
            $row = (array) $row;
            $row_date = $row['date'] ?? null;
            
            // Calculate pre-filter opening balance
            if ($from && $row_date && $row_date < $from) {
                $pre_filter_balance += $in - $out;
                continue;
            }
            
            $opening_balance += $in - $out;
            
            // Date filtering (skip if not in range)
            if ($row_date) {
                if ($from && $row_date < $from) continue;
                if ($to && $row_date > $to) continue;
            }
            
            $row['balance'] = $opening_balance;
            $rows[] = $row;
        }
        
        // Insert running opening balance row if filter is applied
        if ($from) {
            array_unshift($rows, [
                'date' => $from,
                'party' => 'Opening (as of ' . $from . ')',
                'in' => null,
                'out' => null,
                'rate' => null,
                'balance' => $pre_filter_balance,
            ]);
            
            // Recalculate balances for filtered rows
            $running = $pre_filter_balance;
            foreach ($rows as $i => $row) {
                if ($i === 0) continue; // skip the synthetic opening row
                $in = isset($row['in']) ? ($row['in'] ?: 0) : 0;
                $out = isset($row['out']) ? ($row['out'] ?: 0) : 0;
                $running += $in - $out;
                $rows[$i]['balance'] = $running;
            }
        }

        $pdf = \PDF::loadView('inventory.ledger_pdf', [
            'inventory' => $inventory,
            'rows' => $rows,
            'from' => $from,
            'to' => $to
        ]);

        return $pdf->download('inventory_ledger_' . $inventory->id . '_' . date('Y-m-d') . '.pdf');
    }

    public function showWithoutRate($id)
    {
        $inventory = Inventory::findOrFail($id);

        // Use a single raw SQL to merge opening, purchases, and sales, ordered by date
        $sql = "
            SELECT
                '2025-01-01' AS date,
                'Opening' AS party,
                inventories.opening_qty AS `in`,
                NULL AS `out`
            FROM inventories
            WHERE inventories.id = ?

            UNION ALL

            SELECT
                purchases.purchase_date AS date,
                vendors.name AS party,
                purchase_items.quantity AS `in`,
                NULL AS `out`
            FROM purchase_items
            INNER JOIN purchases ON purchase_items.purchase_id = purchases.id
            INNER JOIN vendors ON purchases.vendor_id = vendors.id
            WHERE purchase_items.product_id = ?

            UNION ALL

            SELECT
                sales.sale_date AS date,
                customers.name AS party,
                NULL AS `in`,
                sale_items.quantity AS `out`
            FROM sale_items
            INNER JOIN sales ON sale_items.sale_id = sales.id
            INNER JOIN customers ON sales.customer_id = customers.id
            WHERE sale_items.product_id = ?

            ORDER BY date IS NULL, date ASC
        ";

        // Date filter
        $from = request('from');
        $to = request('to');

        $results = \DB::select($sql, [$inventory->id, $inventory->id, $inventory->id]);

        // Calculate running balance and filter by date
        $balance = 0;
        $rows = [];
        $opening_balance = 0;
        $pre_filter_balance = 0;
        
        foreach ($results as $row) {
            $in = isset($row->in) ? ($row->in ?: 0) : 0;
            $out = isset($row->out) ? ($row->out ?: 0) : 0;
            $row = (array) $row;
            $row_date = $row['date'] ?? null;
            
            // Calculate pre-filter opening balance
            if ($from && $row_date && $row_date < $from) {
                $pre_filter_balance += $in - $out;
                continue;
            }
            
            $opening_balance += $in - $out;
            
            // Date filtering (skip if not in range)
            if ($row_date) {
                if ($from && $row_date < $from) continue;
                if ($to && $row_date > $to) continue;
            }
            
            $row['balance'] = $opening_balance;
            $rows[] = $row;
        }
        
        // Insert running opening balance row if filter is applied
        if ($from) {
            array_unshift($rows, [
                'date' => $from,
                'party' => 'Opening (as of ' . $from . ')',
                'in' => null,
                'out' => null,
                'balance' => $pre_filter_balance,
            ]);
            
            // Recalculate balances for filtered rows
            $running = $pre_filter_balance;
            foreach ($rows as $i => $row) {
                if ($i === 0) continue; // skip the synthetic opening row
                $in = isset($row['in']) ? ($row['in'] ?: 0) : 0;
                $out = isset($row['out']) ? ($row['out'] ?: 0) : 0;
                $running += $in - $out;
                $rows[$i]['balance'] = $running;
            }
        }

        // Pagination (20 per page)
        $page = request()->input('page', 1);
        $perPage = 20;
        $pagedRows = new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($rows, ($page - 1) * $perPage, $perPage),
            count($rows),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('inventory.ledger_without_rate', [
            'inventory' => $inventory,
            'rows' => $pagedRows,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function exportPdfWithoutRate($id, Request $request)
    {
        $inventory = Inventory::findOrFail($id);
        
        // Date filter
        $from = $request->input('from');
        $to = $request->input('to');
        
        // Use a single raw SQL to merge opening, purchases, and sales, ordered by date
        $sql = "
            SELECT
                '2025-01-01' AS date,
                'Opening' AS party,
                inventories.opening_qty AS `in`,
                NULL AS `out`
            FROM inventories
            WHERE inventories.id = ?

            UNION ALL

            SELECT
                purchases.purchase_date AS date,
                vendors.name AS party,
                purchase_items.quantity AS `in`,
                NULL AS `out`
            FROM purchase_items
            INNER JOIN purchases ON purchase_items.purchase_id = purchases.id
            INNER JOIN vendors ON purchases.vendor_id = vendors.id
            WHERE purchase_items.product_id = ?

            UNION ALL

            SELECT
                sales.sale_date AS date,
                customers.name AS party,
                NULL AS `in`,
                sale_items.quantity AS `out`
            FROM sale_items
            INNER JOIN sales ON sale_items.sale_id = sales.id
            INNER JOIN customers ON sales.customer_id = customers.id
            WHERE sale_items.product_id = ?

            ORDER BY date IS NULL, date ASC
        ";

        $results = \DB::select($sql, [$inventory->id, $inventory->id, $inventory->id]);

        // Calculate running balance and filter by date
        $balance = 0;
        $rows = [];
        $opening_balance = 0;
        $pre_filter_balance = 0;
        
        foreach ($results as $row) {
            $in = isset($row->in) ? ($row->in ?: 0) : 0;
            $out = isset($row->out) ? ($row->out ?: 0) : 0;
            $row = (array) $row;
            $row_date = $row['date'] ?? null;
            
            // Calculate pre-filter opening balance
            if ($from && $row_date && $row_date < $from) {
                $pre_filter_balance += $in - $out;
                continue;
            }
            
            $opening_balance += $in - $out;
            
            // Date filtering (skip if not in range)
            if ($row_date) {
                if ($from && $row_date < $from) continue;
                if ($to && $row_date > $to) continue;
            }
            
            $row['balance'] = $opening_balance;
            $rows[] = $row;
        }
        
        // Insert running opening balance row if filter is applied
        if ($from) {
            array_unshift($rows, [
                'date' => $from,
                'party' => 'Opening (as of ' . $from . ')',
                'in' => null,
                'out' => null,
                'balance' => $pre_filter_balance,
            ]);
            
            // Recalculate balances for filtered rows
            $running = $pre_filter_balance;
            foreach ($rows as $i => $row) {
                if ($i === 0) continue; // skip the synthetic opening row
                $in = isset($row['in']) ? ($row['in'] ?: 0) : 0;
                $out = isset($row['out']) ? ($row['out'] ?: 0) : 0;
                $running += $in - $out;
                $rows[$i]['balance'] = $running;
            }
        }

        $pdf = \PDF::loadView('inventory.ledger_without_rate_pdf', [
            'inventory' => $inventory,
            'rows' => $rows,
            'from' => $from,
            'to' => $to
        ]);

        return $pdf->download('inventory_ledger_without_rate_' . $inventory->id . '_' . date('Y-m-d') . '.pdf');
    }
}

