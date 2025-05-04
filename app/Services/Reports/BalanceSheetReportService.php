<?php

namespace App\Services\Reports;

use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;

class BalanceSheetReportService
{
    /**
     * Get Balance Sheet as of a date
     * @param string $asOfDate
     * @return array
     */
    public function getBalanceSheet($asOfDate)
    {
        $types = ['Asset', 'Liability', 'Equity'];
        $result = [];
        $totals = ['Asset' => 0, 'Liability' => 0, 'Equity' => 0];
        foreach ($types as $type) {
            $accounts = ChartOfAccount::where('type', $type)->get();
            $accountsArr = [];
            foreach ($accounts as $acc) {
                $lines = JournalEntryLine::where('account_id', $acc->id)
                    ->whereHas('journalEntry', function($q) use ($asOfDate) {
                        $q->where('date', '<=', $asOfDate);
                    });
                $debit = $lines->sum('debit');
                $credit = $lines->sum('credit');
                if ($acc->nature === 'debit') {
                    $balance = $debit - $credit;
                } else {
                    $balance = $credit - $debit;
                }
                $accountsArr[] = [
                    'account_id' => $acc->id,
                    'account_code' => $acc->code,
                    'account_name' => $acc->name,
                    'balance' => $balance
                ];
                $totals[$type] += $balance;
            }
            $result[$type] = $accountsArr;
        }
        // Validate accounting equation
        $isBalanced = ($totals['Asset'] == ($totals['Liability'] + $totals['Equity']));
        return [
            'as_of_date' => $asOfDate,
            'accounts' => $result,
            'totals' => $totals,
            'is_balanced' => $isBalanced,
        ];
    }
}
