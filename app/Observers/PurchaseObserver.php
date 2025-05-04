<?php

namespace App\Observers;

use App\Models\Purchase;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

class PurchaseObserver
{
    public function created(Purchase $purchase)
    {
        // Only proceed if there are items and net_amount > 0
        if ($purchase->net_amount <= 0 || $purchase->items()->count() == 0) {
            return;
        }
        $amount = $purchase->total_amount;
        $discount = $purchase->discount_amount ?? 0;
        $net = $purchase->net_amount;
        // Get relevant accounts
        $inventory = ChartOfAccount::where('type', 'Asset')->where(function($q) {
            $q->where('code', '1003')->orWhere('name', 'Inventory');
        })->first();
        $payable = ChartOfAccount::where('type', 'Liability')->where(function($q) {
            $q->where('code', '2001')->orWhere('name', 'Accounts Payable');
        })->first();
        $discountAccount = ChartOfAccount::where('type', 'Income')->where(function($q) {
            $q->where('name', 'Discount Received');
        })->first();
        $cashOrBank = null;
        if ($purchase->payment_account_id) {
            $cashOrBank = ChartOfAccount::find($purchase->payment_account_id);
        }
        if (!$inventory || (!$payable && !$cashOrBank)) {
            throw new \Exception('Required account(s) not found for purchase journal entry.');
        }
        // Generate purchase number
        $last = JournalEntry::where('entry_number', 'like', 'PUR-%')->orderByDesc('id')->first();
        $nextNum = $last ? (intval(substr($last->entry_number, 4)) + 1) : 1;
        $entryNumber = 'PUR-' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);
        DB::transaction(function() use ($purchase, $inventory, $payable, $discountAccount, $cashOrBank, $amount, $discount, $net, $entryNumber) {
            $entry = new JournalEntry([
                'entry_number' => $entryNumber,
                'date' => $purchase->purchase_date ?? now(),
                'description' => 'Purchase from Vendor: ' . optional($purchase->vendor)->name,
                'created_by' => 1, // Default user
                'reference_type' => 'Purchase',
                'reference_id' => $purchase->id,
            ]);
            $entry->save();
            $lines = [
                new JournalEntryLine([
                    'account_id' => $inventory->id,
                    'debit' => $amount,
                    'credit' => null,
                    'description' => 'Inventory for purchase',
                ])
            ];
            // Discount received (if any)
            if ($discount > 0 && $discountAccount) {
                $lines[] = new JournalEntryLine([
                    'account_id' => $discountAccount->id,
                    'debit' => null,
                    'credit' => $discount,
                    'description' => 'Discount Received',
                ]);
            }
            // Credit side: Payable or Cash/Bank
            if ($purchase->payment_status === 'Paid' && $cashOrBank) {
                $lines[] = new JournalEntryLine([
                    'account_id' => $cashOrBank->id,
                    'debit' => null,
                    'credit' => $net,
                    'description' => 'Paid via Cash/Bank',
                ]);
            } else if ($payable) {
                $lines[] = new JournalEntryLine([
                    'account_id' => $payable->id,
                    'debit' => null,
                    'credit' => $net,
                    'description' => 'Accounts Payable',
                ]);
            }
            $entry->lines()->saveMany($lines);
            if (!$entry->isBalanced()) {
                throw new \Exception('Purchase journal entry is not balanced.');
            }
        });
    }
}
