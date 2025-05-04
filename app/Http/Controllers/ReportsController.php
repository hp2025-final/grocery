<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class ReportsController extends Controller {
    public function trialBalance(Request $request) {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $accountType = $request->input('account_type');

        // Get only main accounts (no subaccounts)
        $accounts = \App\Models\ChartOfAccount::whereNull('parent_id')->where('is_group', 0)->orderBy('code')->get();
        $rows = [];

        foreach ($accounts as $account) {
            if ($accountType && strtolower($account->type) !== strtolower($accountType)) continue;

            // Check for subaccounts
            $subs = \App\Models\ChartOfAccount::where('parent_id', $account->id)->get();
            if ($subs->count() > 0) {
                $opening_balance = 0;
                $periodDebits = 0;
                $periodCredits = 0;
                foreach ($subs as $sub) {
                    $opening = $sub->opening_balance ?? 0;
                    $debitsBefore = 0;
                    $creditsBefore = 0;
                    if ($fromDate) {
                        $debitsBefore = \App\Models\JournalEntryLine::where('account_id', $sub->id)
                            ->whereHas('journalEntry', function($q) use ($fromDate) { $q->whereDate('date', '<', $fromDate); })
                            ->sum('debit');
                        $creditsBefore = \App\Models\JournalEntryLine::where('account_id', $sub->id)
                            ->whereHas('journalEntry', function($q) use ($fromDate) { $q->whereDate('date', '<', $fromDate); })
                            ->sum('credit');
                    }
                    $sub_opening = $opening + $debitsBefore - $creditsBefore;
                    if (in_array($sub->type, ['Liability','Equity','Income'])) {
                        $sub_opening = $opening + $creditsBefore - $debitsBefore;
                    }
                    $opening_balance += $sub_opening;

                    $debits = \App\Models\JournalEntryLine::where('account_id', $sub->id);
                    $credits = \App\Models\JournalEntryLine::where('account_id', $sub->id);
                    if ($fromDate) {
                        $debits = $debits->whereHas('journalEntry', function($q) use ($fromDate) { $q->whereDate('date', '>=', $fromDate); });
                        $credits = $credits->whereHas('journalEntry', function($q) use ($fromDate) { $q->whereDate('date', '>=', $fromDate); });
                    }
                    if ($toDate) {
                        $debits = $debits->whereHas('journalEntry', function($q) use ($toDate) { $q->whereDate('date', '<=', $toDate); });
                        $credits = $credits->whereHas('journalEntry', function($q) use ($toDate) { $q->whereDate('date', '<=', $toDate); });
                    }
                    $periodDebits += $debits->sum('debit');
                    $periodCredits += $credits->sum('credit');
                }
                $closing_balance = $opening_balance + $periodDebits - $periodCredits;
                if (in_array($account->type, ['Liability','Equity','Income'])) {
                    $closing_balance = $opening_balance + $periodCredits - $periodDebits;
                }
            } else {
                // No subaccounts, use main account only
                $opening = $account->opening_balance ?? 0;
                $debitsBefore = 0;
                $creditsBefore = 0;
                if ($fromDate) {
                    $debitsBefore = \App\Models\JournalEntryLine::where('account_id', $account->id)
                        ->whereHas('journalEntry', function($q) use ($fromDate) { $q->whereDate('date', '<', $fromDate); })
                        ->sum('debit');
                    $creditsBefore = \App\Models\JournalEntryLine::where('account_id', $account->id)
                        ->whereHas('journalEntry', function($q) use ($fromDate) { $q->whereDate('date', '<', $fromDate); })
                        ->sum('credit');
                }
                $opening_balance = $opening + $debitsBefore - $creditsBefore;
                if (in_array($account->type, ['Liability','Equity','Income'])) {
                    $opening_balance = $opening + $creditsBefore - $debitsBefore;
                }

                $debits = \App\Models\JournalEntryLine::where('account_id', $account->id);
                $credits = \App\Models\JournalEntryLine::where('account_id', $account->id);
                if ($fromDate) {
                    $debits = $debits->whereHas('journalEntry', function($q) use ($fromDate) { $q->whereDate('date', '>=', $fromDate); });
                    $credits = $credits->whereHas('journalEntry', function($q) use ($fromDate) { $q->whereDate('date', '>=', $fromDate); });
                }
                if ($toDate) {
                    $debits = $debits->whereHas('journalEntry', function($q) use ($toDate) { $q->whereDate('date', '<=', $toDate); });
                    $credits = $credits->whereHas('journalEntry', function($q) use ($toDate) { $q->whereDate('date', '<=', $toDate); });
                }
                $periodDebits = $debits->sum('debit');
                $periodCredits = $credits->sum('credit');

                $closing_balance = $opening_balance + $periodDebits - $periodCredits;
                if (in_array($account->type, ['Liability','Equity','Income'])) {
                    $closing_balance = $opening_balance + $periodCredits - $periodDebits;
                }
            }

            $rows[] = [
                'account_code' => $account->code,
                'account_name' => $account->name,
                'type' => $account->type,
                'opening_balance' => $opening_balance,
                'total_debit' => $periodDebits,
                'total_credit' => $periodCredits,
                'closing_balance' => $closing_balance,
            ];
        }

        return view('reports.trial_balance', compact('rows'));
    }
    public function generalLedger(Request $request) {
        $accounts = \App\Models\ChartOfAccount::orderBy('name')->get();
        $selectedAccountId = $request->input('account');
        $from = $request->input('from');
        $to = $request->input('to');
        $ledger = null;
        if ($selectedAccountId) {
            $service = app(\App\Services\Reports\GeneralLedgerReportService::class);
            $ledger = $service->getGeneralLedger($selectedAccountId, $from, $to);
        }
        return view('reports.general_ledger', compact('accounts', 'ledger', 'selectedAccountId', 'from', 'to'));
    }
    public function journal(Request $request) {
        $query = \App\Models\JournalEntry::with(['lines.account']);
        $from = $request->input('from');
        $to = $request->input('to');
        
        if ($from) {
            $query->whereDate('date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('date', '<=', $to);
        }
        
        $entries = $query->orderByDesc('date')
                         ->orderByDesc('id')
                         ->paginate(20);
                         
        return view('reports.journal', compact('entries', 'from', 'to'));
    }
    public function incomeStatement(Request $request) {
        $from = $request->input('from');
        $to = $request->input('to');

        // Income
        $incomeAccounts = \App\Models\ChartOfAccount::where('type', 'income')->pluck('id');
        $incomeQuery = \App\Models\JournalEntryLine::whereIn('account_id', $incomeAccounts);
        if ($from) $incomeQuery->whereHas('journalEntry', function($q) use ($from) { $q->whereDate('date', '>=', $from); });
        if ($to) $incomeQuery->whereHas('journalEntry', function($q) use ($to) { $q->whereDate('date', '<=', $to); });
        $totalIncome = $incomeQuery->sum('credit');

        // COGS (Cost of Goods Sold)
        $cogsAccount = \App\Models\ChartOfAccount::where('code', '5005')->orWhere('name', 'Cost of Goods Sold')->first();
        $cogs = 0;
        if ($cogsAccount) {
            $cogsQuery = \App\Models\JournalEntryLine::where('account_id', $cogsAccount->id);
            if ($from) $cogsQuery->whereHas('journalEntry', function($q) use ($from) { $q->whereDate('date', '>=', $from); });
            if ($to) $cogsQuery->whereHas('journalEntry', function($q) use ($to) { $q->whereDate('date', '<=', $to); });
            $cogs = $cogsQuery->sum('debit');
        }

        // All Expenses (for operating expenses, exclude COGS)
        $expenseAccounts = \App\Models\ChartOfAccount::where('type', 'expense')->pluck('id');
        $operatingExpenseAccounts = $expenseAccounts->filter(function($id) use ($cogsAccount) {
            return !$cogsAccount || $id != $cogsAccount->id;
        });
        $operatingExpenseQuery = \App\Models\JournalEntryLine::whereIn('account_id', $operatingExpenseAccounts);
        if ($from) $operatingExpenseQuery->whereHas('journalEntry', function($q) use ($from) { $q->whereDate('date', '>=', $from); });
        if ($to) $operatingExpenseQuery->whereHas('journalEntry', function($q) use ($to) { $q->whereDate('date', '<=', $to); });
        $operatingExpenses = $operatingExpenseQuery->sum('debit');

        // Gross Profit and Operating Profit
        $grossProfit = $totalIncome - $cogs;
        $operatingProfit = $grossProfit - $operatingExpenses;

        return view('reports.income_statement', compact('totalIncome', 'cogs', 'grossProfit', 'operatingExpenses', 'operatingProfit', 'from', 'to'));
    }
    public function balanceSheet(Request $request) {
        $asOf = $request->input('as_of') ?: date('Y-m-d');

        // Main accounts only (no subaccounts)
        $mainAccounts = \App\Models\ChartOfAccount::whereNull('parent_id')->where('is_group', 0)->get();

        $assetAccounts = $mainAccounts->where('type', 'Asset');
        $liabilityAccounts = $mainAccounts->where('type', 'Liability');
        $equityAccounts = $mainAccounts->where('type', 'Equity');

        // ASSETS
        $assets = [];
        $totalAssets = 0;
        foreach ($assetAccounts as $account) {
            // Get subaccounts
            $subs = \App\Models\ChartOfAccount::where('parent_id', $account->id)->get();
            if ($subs->count() > 0) {
                $balance = 0;
                foreach ($subs as $sub) {
                    $debits = \App\Models\JournalEntryLine::where('account_id', $sub->id)
                        ->whereHas('journalEntry', function($q) use ($asOf) { $q->whereDate('date', '<=', $asOf); })
                        ->sum('debit');
                    $credits = \App\Models\JournalEntryLine::where('account_id', $sub->id)
                        ->whereHas('journalEntry', function($q) use ($asOf) { $q->whereDate('date', '<=', $asOf); })
                        ->sum('credit');
                    $opening = $sub->opening_balance ?? 0;
                    $balance += $opening + $debits - $credits;
                }
            } else {
                $debits = \App\Models\JournalEntryLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', function($q) use ($asOf) { $q->whereDate('date', '<=', $asOf); })
                    ->sum('debit');
                $credits = \App\Models\JournalEntryLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', function($q) use ($asOf) { $q->whereDate('date', '<=', $asOf); })
                    ->sum('credit');
                $opening = $account->opening_balance ?? 0;
                $balance = $opening + $debits - $credits;
            }
            $assets[] = ['name' => $account->name, 'amount' => $balance];
            $totalAssets += $balance;
        }

        // LIABILITIES
        $liabilities = [];
        $totalLiabilities = 0;
        foreach ($liabilityAccounts as $account) {
            $subs = \App\Models\ChartOfAccount::where('parent_id', $account->id)->get();
            if ($subs->count() > 0) {
                $balance = 0;
                foreach ($subs as $sub) {
                    $debits = \App\Models\JournalEntryLine::where('account_id', $sub->id)
                        ->whereHas('journalEntry', function($q) use ($asOf) { $q->whereDate('date', '<=', $asOf); })
                        ->sum('debit');
                    $credits = \App\Models\JournalEntryLine::where('account_id', $sub->id)
                        ->whereHas('journalEntry', function($q) use ($asOf) { $q->whereDate('date', '<=', $asOf); })
                        ->sum('credit');
                    $opening = $sub->opening_balance ?? 0;
                    $balance += $opening + $credits - $debits;
                }
            } else {
                $debits = \App\Models\JournalEntryLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', function($q) use ($asOf) { $q->whereDate('date', '<=', $asOf); })
                    ->sum('debit');
                $credits = \App\Models\JournalEntryLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', function($q) use ($asOf) { $q->whereDate('date', '<=', $asOf); })
                    ->sum('credit');
                $opening = $account->opening_balance ?? 0;
                $balance = $opening + $credits - $debits;
            }
            $liabilities[] = ['name' => $account->name, 'amount' => $balance];
            $totalLiabilities += $balance;
        }

        // EQUITY
        $equity = [];
        $totalEquity = 0;
        foreach ($equityAccounts as $account) {
            $subs = \App\Models\ChartOfAccount::where('parent_id', $account->id)->get();
            if ($subs->count() > 0) {
                $balance = 0;
                foreach ($subs as $sub) {
                    $debits = \App\Models\JournalEntryLine::where('account_id', $sub->id)
                        ->whereHas('journalEntry', function($q) use ($asOf) { $q->whereDate('date', '<=', $asOf); })
                        ->sum('debit');
                    $credits = \App\Models\JournalEntryLine::where('account_id', $sub->id)
                        ->whereHas('journalEntry', function($q) use ($asOf) { $q->whereDate('date', '<=', $asOf); })
                        ->sum('credit');
                    $opening = $sub->opening_balance ?? 0;
                    $balance += $opening + $credits - $debits;
                }
            } else {
                $debits = \App\Models\JournalEntryLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', function($q) use ($asOf) { $q->whereDate('date', '<=', $asOf); })
                    ->sum('debit');
                $credits = \App\Models\JournalEntryLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', function($q) use ($asOf) { $q->whereDate('date', '<=', $asOf); })
                    ->sum('credit');
                $opening = $account->opening_balance ?? 0;
                $balance = $opening + $credits - $debits;
            }
            $equity[] = ['name' => $account->name, 'amount' => $balance];
            $totalEquity += $balance;
        }

        // Retained Earnings / Net Profit (from income statement logic)
        // Income
        $incomeAccounts = \App\Models\ChartOfAccount::where('type', 'Income')->pluck('id');
        $incomeQuery = \App\Models\JournalEntryLine::whereIn('account_id', $incomeAccounts);
        if ($asOf) $incomeQuery->whereHas('journalEntry', function($q) use ($asOf) { $q->whereDate('date', '<=', $asOf); });
        $totalIncome = $incomeQuery->sum('credit');
        // Expenses
        $expenseAccounts = \App\Models\ChartOfAccount::where('type', 'Expense')->pluck('id');
        $expenseQuery = \App\Models\JournalEntryLine::whereIn('account_id', $expenseAccounts);
        if ($asOf) $expenseQuery->whereHas('journalEntry', function($q) use ($asOf) { $q->whereDate('date', '<=', $asOf); });
        $totalExpenses = $expenseQuery->sum('debit');
        $netProfit = $totalIncome - $totalExpenses;
        $equity[] = ['name' => 'Retained Earnings / Net Profit', 'amount' => $netProfit];
        $totalEquity += $netProfit;

        // Final Balancing
        $finalAssets = $totalAssets;
        $finalLiabilitiesEquity = $totalLiabilities + $totalEquity;

        return view('reports.balance_sheet', compact(
            'assets', 'liabilities', 'equity',
            'totalAssets', 'totalLiabilities', 'totalEquity',
            'finalAssets', 'finalLiabilitiesEquity', 'asOf'
        ));
    }
}

