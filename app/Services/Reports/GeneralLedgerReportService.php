<?php

namespace App\Services\Reports;

use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

class GeneralLedgerReportService
{
    /**
     * Get general ledger for an account and date range
     * @param int $accountId
     * @param string|null $from
     * @param string|null $to
     * @return array
     */
    public function getGeneralLedger($accountId, $from = null, $to = null)
    {
        $account = ChartOfAccount::findOrFail($accountId);
        // Opening balance before $from
        $opening = JournalEntryLine::where('account_id', $accountId);
        if ($from) {
            $opening->whereHas('journalEntry', function($q) use ($from) {
                $q->where('date', '<', $from);
            });
        }
        $openingDebit = $opening->sum('debit');
        $openingCredit = $opening->sum('credit');
        $openingBalance = ($account->nature === 'debit') ? ($openingDebit - $openingCredit) : ($openingCredit - $openingDebit);
        // Transactions in range
        $lines = JournalEntryLine::with('journalEntry')
            ->where('account_id', $accountId)
            ->when($from, function($q) use ($from) {
                $q->whereHas('journalEntry', function($q2) use ($from) {
                    $q2->where('date', '>=', $from);
                });
            })
            ->when($to, function($q) use ($to) {
                $q->whereHas('journalEntry', function($q2) use ($to) {
                    $q2->where('date', '<=', $to);
                });
            })
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->orderBy('journal_entries.date')
            ->select('journal_entry_lines.*')
            ->get();
        $result = [];
        $runningBalance = $openingBalance;
        foreach ($lines as $line) {
            $runningBalance += ($line->debit ?? 0) - ($line->credit ?? 0);
            $result[] = [
                'date' => $line->journalEntry->date,
                'journal_number' => $line->journalEntry->entry_number,
                'description' => $line->journalEntry->description,
                'debit' => $line->debit,
                'credit' => $line->credit,
                'balance' => $runningBalance,
            ];
        }
        return [
            'account_id' => $account->id,
            'account_code' => $account->code,
            'account_name' => $account->name,
            'opening_balance' => $openingBalance,
            'transactions' => $result,
        ];
    }
}
