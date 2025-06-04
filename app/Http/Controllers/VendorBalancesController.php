<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\Purchase;
use App\Models\VendorPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PDF;

class VendorBalancesController extends Controller
{
    public function index(Request $request)
    {
        // Get date filters, default to 2025-01-01 and current date
        $from = $request->input('from', '2025-01-01');
        $to = $request->input('to', date('Y-m-d'));

        $vendors = DB::table('vendors')
            ->select([
                'vendors.id',
                'vendors.name',
                'vendors.account_id',
                // Get opening balance (all transactions before filter date)
                DB::raw('COALESCE(SUM(
                    CASE 
                        WHEN journal_entries.date < "' . $from . '" 
                        THEN (COALESCE(journal_entry_lines.credit, 0) - COALESCE(journal_entry_lines.debit, 0))
                        ELSE 0 
                    END
                ), 0) as opening_balance'),
                // Current period transactions - For vendors, credits are purchases (PR.DR) and debits are payments (PR.CR)
                DB::raw('COALESCE(SUM(
                    CASE 
                        WHEN journal_entries.date >= "' . $from . '" 
                        AND journal_entries.date <= "' . $to . '"
                        THEN COALESCE(journal_entry_lines.credit, 0)
                        ELSE 0 
                    END
                ), 0) as period_credits'),
                DB::raw('COALESCE(SUM(
                    CASE 
                        WHEN journal_entries.date >= "' . $from . '" 
                        AND journal_entries.date <= "' . $to . '"
                        THEN COALESCE(journal_entry_lines.debit, 0)
                        ELSE 0 
                    END
                ), 0) as period_debits')
            ])
            ->leftJoin('journal_entry_lines', 'vendors.account_id', '=', 'journal_entry_lines.account_id')
            ->leftJoin('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->groupBy('vendors.id', 'vendors.name', 'vendors.account_id')
            ->get()
            ->map(function ($vendor) {
                return (object)[
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                    'opening_balance' => $vendor->opening_balance,
                    'current_month_purchases' => $vendor->period_credits, // Show credits as PR.DR (purchases)
                    'current_month_payments' => $vendor->period_debits,  // Show debits as PR.CR (payments)
                    'closing_balance' => $vendor->opening_balance + ($vendor->period_credits - $vendor->period_debits)
                ];
            });

        return view('vendor-balances.index', [
            'vendors' => $vendors,
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

        $vendors = DB::table('vendors')
            ->select([
                'vendors.id',
                'vendors.name',
                'vendors.account_id',
                // Get opening balance (all transactions before filter date)
                DB::raw('COALESCE(SUM(
                    CASE 
                        WHEN journal_entries.date < "' . $from . '" 
                        THEN (COALESCE(journal_entry_lines.credit, 0) - COALESCE(journal_entry_lines.debit, 0))
                        ELSE 0 
                    END
                ), 0) as opening_balance'),
                // Current period transactions - For vendors, credits are purchases (PR.DR) and debits are payments (PR.CR)
                DB::raw('COALESCE(SUM(
                    CASE 
                        WHEN journal_entries.date >= "' . $from . '" 
                        AND journal_entries.date <= "' . $to . '"
                        THEN COALESCE(journal_entry_lines.credit, 0)
                        ELSE 0 
                    END
                ), 0) as period_credits'),
                DB::raw('COALESCE(SUM(
                    CASE 
                        WHEN journal_entries.date >= "' . $from . '" 
                        AND journal_entries.date <= "' . $to . '"
                        THEN COALESCE(journal_entry_lines.debit, 0)
                        ELSE 0 
                    END
                ), 0) as period_debits')
            ])
            ->leftJoin('journal_entry_lines', 'vendors.account_id', '=', 'journal_entry_lines.account_id')
            ->leftJoin('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->groupBy('vendors.id', 'vendors.name', 'vendors.account_id')
            ->get()
            ->map(function ($vendor) {
                return (object)[
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                    'opening_balance' => $vendor->opening_balance,
                    'current_month_purchases' => $vendor->period_credits, // Show credits as PR.DR (purchases)
                    'current_month_payments' => $vendor->period_debits,  // Show debits as PR.CR (payments)
                    'closing_balance' => $vendor->opening_balance + ($vendor->period_credits - $vendor->period_debits)
                ];
            });

        $pdf = PDF::loadView('pdfs.vendor-balances', [
            'vendors' => $vendors,
            'from' => $from,
            'to' => $to
        ]);

        return $pdf->download('vendor-balances-' . $from . '-to-' . $to . '.pdf');
    }
}