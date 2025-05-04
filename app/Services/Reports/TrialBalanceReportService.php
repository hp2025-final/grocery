<?php

namespace App\Services\Reports;

use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

class TrialBalanceReportService
{
    /**
     * Get trial balance for a date range and account type(s)
     * @param string|null $from
     * @param string|null $to
     * @param array|null $accountTypes
     * @return array
     */
    public function getTrialBalance($from = null, $to = null, $accountTypes = null)
    {
        $accounts = ChartOfAccount::query();
        if ($accountTypes) {
            $accounts->whereIn('type', $accountTypes);
        }
        $accounts = $accounts->get();
        $result = [];
        foreach ($accounts as $account) {
            // Opening balance: sum of all previous debits - credits before $from
            $opening = JournalEntryLine::where('account_id', $account->id);
            if ($from) {
                $opening->whereHas('journalEntry', function($q) use ($from) {
                    $q->where('date', '<', $from);
                });
            }
            $openingDebit = $opening->sum('debit');
            $openingCredit = $opening->sum('credit');
            // Determine normal balance by account type
            if (in_array($account->type, ['Asset', 'Expense'])) {
                $openingBalance = $openingDebit - $openingCredit;
            } else {
                $openingBalance = $openingCredit - $openingDebit;
            }
            // Period transactions
            $lines = JournalEntryLine::where('account_id', $account->id);
            if ($from) {
                $lines->whereHas('journalEntry', function($q) use ($from) {
                    $q->where('date', '>=', $from);
                });
            }
            if ($to) {
                $lines->whereHas('journalEntry', function($q) use ($to) {
                    $q->where('date', '<=', $to);
                });
            }
            $periodDebit = $lines->sum('debit');
            $periodCredit = $lines->sum('credit');
            // Closing balance
            // Calculate closing balance with correct sign for account type
            if (in_array($account->type, ['Asset', 'Expense'])) {
                $closingBalance = $openingBalance + $periodDebit - $periodCredit;
            } else {
                $closingBalance = $openingBalance + $periodCredit - $periodDebit;
            }
            $result[] = [
                'account_id' => $account->id,
                'account_code' => $account->code,
                'account_name' => $account->name,
                'type' => $account->type,

                'opening_balance' => $openingBalance,
                'total_debit' => $periodDebit,
                'total_credit' => $periodCredit,
                'closing_balance' => $closingBalance,
            ];
        }
        return $result;
    }
}
