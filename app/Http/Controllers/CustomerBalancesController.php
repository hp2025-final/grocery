<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PDF;

class CustomerBalancesController extends Controller
{
    public function index(Request $request)
    {
        // Get date filters, default to 2025-01-01 and current date
        $from = $request->input('from', '2025-01-01');
        $to = $request->input('to', date('Y-m-d'));

        $customers = DB::table('customers')
            ->select([
                'customers.id',
                'customers.name',
                'customers.account_id',
                // Get opening balance (all transactions before filter date)
                DB::raw('COALESCE(SUM(
                    CASE 
                        WHEN journal_entries.date < "' . $from . '" 
                        THEN (COALESCE(journal_entry_lines.debit, 0) - COALESCE(journal_entry_lines.credit, 0))
                        ELSE 0 
                    END
                ), 0) as opening_balance'),
                // Current period transactions - For customers, debits are sales (AR.DR) and credits are receipts (AR.CR)
                DB::raw('COALESCE(SUM(
                    CASE 
                        WHEN journal_entries.date >= "' . $from . '" 
                        AND journal_entries.date <= "' . $to . '"
                        THEN COALESCE(journal_entry_lines.debit, 0)
                        ELSE 0 
                    END
                ), 0) as period_debits'),
                DB::raw('COALESCE(SUM(
                    CASE 
                        WHEN journal_entries.date >= "' . $from . '" 
                        AND journal_entries.date <= "' . $to . '"
                        THEN COALESCE(journal_entry_lines.credit, 0)
                        ELSE 0 
                    END
                ), 0) as period_credits')
            ])
            ->leftJoin('journal_entry_lines', 'customers.account_id', '=', 'journal_entry_lines.account_id')
            ->leftJoin('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->groupBy('customers.id', 'customers.name', 'customers.account_id')
            ->get()
            ->map(function ($customer) {
                return (object)[
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'opening_balance' => $customer->opening_balance,
                    'current_month_sales' => $customer->period_debits,    // Show debits as AR.DR (sales)
                    'current_month_receipts' => $customer->period_credits, // Show credits as AR.CR (receipts)
                    'closing_balance' => $customer->opening_balance + ($customer->period_debits - $customer->period_credits)
                ];
            });

        // Return view with data
        return view('customer-balances.index', compact('customers', 'from', 'to'));
    }

    public function exportPdf(Request $request)
    {
        $customers = $this->index($request)->getData()['customers'];
        $from = $request->input('from', '2025-01-01');
        $to = $request->input('to', date('Y-m-d'));

        $pdf = PDF::loadView('reports.customer_balances_pdf', compact('customers', 'from', 'to'));
        return $pdf->download('customer_balances.pdf');
    }
}
