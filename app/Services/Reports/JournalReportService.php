<?php

namespace App\Services\Reports;

use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;

class JournalReportService
{
    /**
     * Get journal entries with lines, filterable by date range and source/type
     * @param string|null $from
     * @param string|null $to
     * @param string|null $referenceType
     * @return array
     */
    public function getJournalReport($from = null, $to = null, $referenceType = null)
    {
        $entries = JournalEntry::with('lines.account')
            ->when($from, function($q) use ($from) {
                $q->where('date', '>=', $from);
            })
            ->when($to, function($q) use ($to) {
                $q->where('date', '<=', $to);
            })
            ->when($referenceType, function($q) use ($referenceType) {
                $q->where('reference_type', $referenceType);
            })
            ->orderBy('date')
            ->orderBy('id')
            ->get();
        $result = [];
        foreach ($entries as $entry) {
            $lines = [];
            foreach ($entry->lines as $line) {
                $lines[] = [
                    'account_code' => $line->account->code ?? null,
                    'account_name' => $line->account->name ?? null,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                    'description' => $line->description,
                ];
            }
            $result[] = [
                'journal_number' => $entry->entry_number,
                'date' => $entry->date,
                'description' => $entry->description,
                'reference_type' => $entry->reference_type,
                'reference_id' => $entry->reference_id,
                'lines' => $lines,
            ];
        }
        return $result;
    }
}
