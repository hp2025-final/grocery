<?php

namespace App\Services\Reports;

use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;

class IncomeStatementService
{
    /**
     * Get Income Statement (Profit & Loss) for a date range
     * @param string $from
     * @param string $to
     * @return array
     */
    public function getIncomeStatement($from, $to)
    {
        // Get all income and expense accounts
        $incomeAccounts = ChartOfAccount::where('type', 'Income')->get();
        $expenseAccounts = ChartOfAccount::where('type', 'Expense')->get();
        $income = [];
        $expense = [];
        $totalIncome = 0;
        $totalExpense = 0;
        foreach ($incomeAccounts as $acc) {
            $lines = JournalEntryLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', function($q) use ($from, $to) {
                    $q->whereBetween('date', [$from, $to]);
                });
            $debit = $lines->sum('debit');
            $credit = $lines->sum('credit');
            $balance = ($acc->nature === 'credit') ? ($credit - $debit) : ($debit - $credit);
            $income[] = [
                'account_id' => $acc->id,
                'account_code' => $acc->code,
                'account_name' => $acc->name,
                'amount' => $balance
            ];
            $totalIncome += $balance;
        }
        foreach ($expenseAccounts as $acc) {
            $lines = JournalEntryLine::where('account_id', $acc->id)
                ->whereHas('journalEntry', function($q) use ($from, $to) {
                    $q->whereBetween('date', [$from, $to]);
                });
            $debit = $lines->sum('debit');
            $credit = $lines->sum('credit');
            $balance = ($acc->nature === 'debit') ? ($debit - $credit) : ($credit - $debit);
            $expense[] = [
                'account_id' => $acc->id,
                'account_code' => $acc->code,
                'account_name' => $acc->name,
                'amount' => $balance
            ];
            $totalExpense += $balance;
        }
        $netProfit = $totalIncome - $totalExpense;
        return [
            'from' => $from,
            'to' => $to,
            'income' => $income,
            'total_income' => $totalIncome,
            'expense' => $expense,
            'total_expense' => $totalExpense,
            'net_profit' => $netProfit
        ];
    }
}
