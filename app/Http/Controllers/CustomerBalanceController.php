<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDF;

class CustomerBalanceController extends Controller
{
    public function index(Request $request)
    {
        // Get date filters, default to current date
        $today = date('Y-m-d');
        $from = $request->input('from', $today);
        $to = $request->input('to', $today);

        // Debug: Get TEST CUSTOMER 1 details
        $testCustomer = DB::table('customers')
            ->where('name', 'TEST CUSTOMER 1')
            ->first();
        
        Log::info('TEST CUSTOMER 1 Basic Info', [
            'customer_id' => $testCustomer->id,
            'account_id' => $testCustomer->account_id,
            'opening_balance' => $testCustomer->opening_balance
        ]);

        // Debug: Get all journal entries for TEST CUSTOMER 1
        $journalEntries = DB::table('journal_entries')
            ->select([
                'journal_entries.id',
                'journal_entries.date',
                'journal_entries.reference_type',
                'journal_entry_lines.debit',
                'journal_entry_lines.credit'
            ])
            ->join('journal_entry_lines', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entry_lines.account_id', $testCustomer->account_id)
            ->orderBy('journal_entries.date')
            ->get();

        Log::info('TEST CUSTOMER 1 Journal Entries', [
            'entries' => $journalEntries->toArray()
        ]);

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
                // Current period transactions - For customers, debits are sales (PR.DR) and credits are payments (PR.CR)
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
                // Debug log for TEST CUSTOMER 1
                if ($customer->name === 'TEST CUSTOMER 1') {
                    Log::info('TEST CUSTOMER 1 Raw Calculations', [
                        'opening_balance' => $customer->opening_balance,
                        'period_debits' => $customer->period_debits,
                        'period_credits' => $customer->period_credits
                    ]);
                }

                return (object)[
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'opening_balance' => $customer->opening_balance,
                    'current_month_sales' => $customer->period_debits,    // Show debits as PR.DR (sales)
                    'current_month_payments' => $customer->period_credits, // Show credits as PR.CR (payments)
                    'closing_balance' => $customer->opening_balance + ($customer->period_debits - $customer->period_credits)
                ];
            });

        return view('customer-balances.index', [
            'customers' => $customers,
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
                // Current period transactions - For customers, debits are sales (PR.DR) and credits are payments (PR.CR)
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
                    'current_month_sales' => $customer->period_debits,    // Show debits as PR.DR (sales)
                    'current_month_payments' => $customer->period_credits, // Show credits as PR.CR (payments)
                    'closing_balance' => $customer->opening_balance + ($customer->period_debits - $customer->period_credits)
                ];
            });

        $pdf = PDF::loadView('pdfs.customer-balances', [
            'customers' => $customers,
            'from' => $from,
            'to' => $to
        ]);

        return $pdf->download('customer-balances-' . $from . '-to-' . $to . '.pdf');
    }
} 