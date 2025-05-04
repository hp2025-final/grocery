<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;

class BankBalancesController extends Controller
{
    public function index(Request $request)
    {
        // Get date filters, default to current date
        $today = date('Y-m-d');
        $from = $request->input('from', $today);
        $to = $request->input('to', $today);

        $banks = DB::table('banks')
            ->select([
                'banks.id',
                'banks.name',
                'banks.account_id',
                // Get opening balance (all transactions before filter date)
                DB::raw('COALESCE(SUM(
                    CASE 
                        WHEN journal_entries.date < "' . $from . '" 
                        THEN (COALESCE(journal_entry_lines.debit, 0) - COALESCE(journal_entry_lines.credit, 0))
                        ELSE 0 
                    END
                ), 0) as opening_balance'),
                // Current period transactions
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
            ->leftJoin('journal_entry_lines', 'banks.account_id', '=', 'journal_entry_lines.account_id')
            ->leftJoin('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->groupBy('banks.id', 'banks.name', 'banks.account_id')
            ->get()
            ->map(function ($bank) {
                return (object)[
                    'id' => $bank->id,
                    'name' => $bank->name,
                    'opening_balance' => $bank->opening_balance,
                    'current_month_receipts' => $bank->period_debits,    // Show debits as receipts
                    'current_month_payments' => $bank->period_credits,   // Show credits as payments
                    'closing_balance' => $bank->opening_balance + ($bank->period_debits - $bank->period_credits)
                ];
            });

        return view('bank-balances.index', [
            'banks' => $banks,
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

        $banks = DB::table('banks')
            ->select([
                'banks.id',
                'banks.name',
                'banks.account_id',
                // Get opening balance (all transactions before filter date)
                DB::raw('COALESCE(SUM(
                    CASE 
                        WHEN journal_entries.date < "' . $from . '" 
                        THEN (COALESCE(journal_entry_lines.debit, 0) - COALESCE(journal_entry_lines.credit, 0))
                        ELSE 0 
                    END
                ), 0) as opening_balance'),
                // Current period transactions
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
            ->leftJoin('journal_entry_lines', 'banks.account_id', '=', 'journal_entry_lines.account_id')
            ->leftJoin('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->groupBy('banks.id', 'banks.name', 'banks.account_id')
            ->get()
            ->map(function ($bank) {
                return (object)[
                    'id' => $bank->id,
                    'name' => $bank->name,
                    'opening_balance' => $bank->opening_balance,
                    'current_month_receipts' => $bank->period_debits,    // Show debits as receipts
                    'current_month_payments' => $bank->period_credits,   // Show credits as payments
                    'closing_balance' => $bank->opening_balance + ($bank->period_debits - $bank->period_credits)
                ];
            });

        $pdf = PDF::loadView('pdfs.bank-balances', [
            'banks' => $banks,
            'from' => $from,
            'to' => $to
        ]);

        return $pdf->download('bank-balances-' . $from . '-to-' . $to . '.pdf');
    }
} 