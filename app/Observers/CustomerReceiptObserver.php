<?php

namespace App\Observers;

use App\Models\CustomerReceipt;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

class CustomerReceiptObserver
{
    public function created(CustomerReceipt $receipt)
    {
        // Journal entry creation logic commented out to prevent duplicate entries.
        /*
        if ($receipt->amount_received <= 0) {
            return;
        }
        // Get accounts
        $cashOrBank = ChartOfAccount::find($receipt->payment_account_id);
        $customerAccount = ChartOfAccount::where('type', 'Asset')->where(function($q) {
            $q->where('code', '1004')->orWhere('name', 'Accounts Receivable (Customers)');
        })->first();
        if (!$cashOrBank || !$customerAccount) {
            throw new \Exception('Required account(s) not found for customer receipt journal entry.');
        }
        // Generate receipt number
        $last = JournalEntry::where('entry_number', 'like', 'RCV-%')->orderByDesc('id')->first();
        $nextNum = $last ? (intval(substr($last->entry_number, 4)) + 1) : 1;
        $entryNumber = 'RCV-' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);
        DB::transaction(function() use ($receipt, $cashOrBank, $customerAccount, $entryNumber) {
            $entry = new JournalEntry([
                'entry_number' => $entryNumber,
                'date' => $receipt->receipt_date ?? now(),
                'description' => 'Customer Receipt: ' . optional($receipt->customer)->name,
                'created_by' => 1, // Default user
                'reference_type' => 'CustomerReceipt',
                'reference_id' => $receipt->id,
            ]);
            $entry->save();
            $lines = [
                new JournalEntryLine([
                    'account_id' => $cashOrBank->id,
                    'debit' => $receipt->amount_received,
                    'credit' => null,
                    'description' => 'Receipt in Cash/Bank',
                ]),
                new JournalEntryLine([
                    'account_id' => $customerAccount->id,
                    'debit' => null,
                    'credit' => $receipt->amount_received,
                    'description' => 'Customer Receivable Cleared',
                ]),
            ];
            $entry->lines()->saveMany($lines);
            if (!$entry->isBalanced()) {
                throw new \Exception('Customer receipt journal entry is not balanced.');
            }
        });
        */
    }
}
