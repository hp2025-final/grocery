<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

class ProductObserver
{
    public function created(Product $product)
    {
        if ($product->opening_quantity > 0 && $product->opening_rate > 0) {
            $amount = bcmul($product->opening_quantity, $product->opening_rate, 2);
            // Get Inventory account (type: Asset, code: 1003 preferred)
            $inventory = ChartOfAccount::where('type', 'Asset')
                ->where(function($q) {
                    $q->where('code', '1003')->orWhere('name', 'Inventory');
                })->first();
            // Get Opening Equity/Capital (type: Equity, code: 3001 preferred)
            $equity = ChartOfAccount::where('type', 'Equity')
                ->where(function($q) {
                    $q->where('code', '3001')->orWhere('name', 'Owner’s Capital')->orWhere('name', 'Opening Balance Equity');
                })->first();
            if (!$inventory || !$equity) {
                throw new \Exception('Required account(s) not found for opening balance journal entry.');
            }
            // Generate entry number
            $last = JournalEntry::orderByDesc('id')->first();
            $nextNum = $last ? (intval(substr($last->entry_number, 4)) + 1) : 1;
            $entryNumber = 'JRN-' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);
            DB::transaction(function() use ($product, $inventory, $equity, $amount, $entryNumber) {
                $entry = new JournalEntry([
                    'entry_number' => $entryNumber,
                    'date' => now(),
                    'description' => 'Opening Balance for Product: ' . $product->name,
                    'created_by' => 1, // Default to first user; adjust as needed
                    'reference_type' => 'Product',
                    'reference_id' => $product->id,
                ]);
                $entry->save();
                $lines = [
                    new JournalEntryLine([
                        'account_id' => $inventory->id,
                        'debit' => $amount,
                        'credit' => null,
                        'description' => 'Inventory for ' . $product->name,
                    ]),
                    new JournalEntryLine([
                        'account_id' => $equity->id,
                        'debit' => null,
                        'credit' => $amount,
                        'description' => 'Opening Equity for ' . $product->name,
                    ]),
                ];
                $entry->lines()->saveMany($lines);
                if (!$entry->isBalanced()) {
                    throw new \Exception('Journal entry is not balanced. Total debits and credits must match.');
                }
            });
        }
    }
}
