<?php

namespace App\Observers;

use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\Product;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

class StockAdjustmentObserver
{
    public function created(StockAdjustment $adjustment)
    {
        $items = $adjustment->items;
        if ($items->isEmpty()) {
            throw new \Exception('No stock adjustment items provided.');
        }
        $totalValue = $items->sum('value');
        if ($totalValue <= 0) {
            throw new \Exception('Stock adjustment value must be positive.');
        }
        // Inventory accounts
        $inventoryAccount = ChartOfAccount::where('type', 'Asset')->where(function($q) {
            $q->where('code', '1201')->orWhere('name', 'Inventory');
        })->first();
        $inventoryAdjAccount = ChartOfAccount::where('name', 'Inventory Adjustment')->first();
        $inventoryLossAccount = ChartOfAccount::where('name', 'Inventory Loss')->first();
        if (!$inventoryAccount) {
            throw new \Exception('Inventory account not found.');
        }
        // Generate adjustment number
        $last = JournalEntry::where('entry_number', 'like', 'ADJ-%')->orderByDesc('id')->first();
        $nextNum = $last ? (intval(substr($last->entry_number, 4)) + 1) : 1;
        $entryNumber = 'ADJ-' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);
        // Stock and journal logic
        DB::transaction(function() use ($adjustment, $items, $inventoryAccount, $inventoryAdjAccount, $inventoryLossAccount, $entryNumber, $totalValue) {
            foreach ($items as $item) {
                $product = Product::find($item->product_id);
                if (!$product) throw new \Exception('Product not found for adjustment.');
                if ($adjustment->adjustment_type === 'Increase') {
                    $product->opening_quantity += $item->quantity;
                } else {
                    if ($product->opening_quantity < $item->quantity) {
                        throw new \Exception('Insufficient stock for product: ' . $product->name);
                    }
                    $product->opening_quantity -= $item->quantity;
                }
                $product->save();
            }
            $entry = new JournalEntry([
                'entry_number' => $entryNumber,
                'date' => $adjustment->adjustment_date ?? now(),
                'description' => 'Stock Adjustment: ' . $adjustment->adjustment_type,
                'created_by' => 1, // Default user
                'reference_type' => 'StockAdjustment',
                'reference_id' => $adjustment->id,
            ]);
            $entry->save();
            if ($adjustment->adjustment_type === 'Increase') {
                if (!$inventoryAdjAccount) throw new \Exception('Inventory Adjustment account not found.');
                $lines = [
                    new JournalEntryLine([
                        'account_id' => $inventoryAccount->id,
                        'debit' => $totalValue,
                        'credit' => null,
                        'description' => 'Inventory Increased',
                    ]),
                    new JournalEntryLine([
                        'account_id' => $inventoryAdjAccount->id,
                        'debit' => null,
                        'credit' => $totalValue,
                        'description' => 'Inventory Adjustment',
                    ]),
                ];
            } else {
                if (!$inventoryLossAccount) throw new \Exception('Inventory Loss account not found.');
                $lines = [
                    new JournalEntryLine([
                        'account_id' => $inventoryLossAccount->id,
                        'debit' => $totalValue,
                        'credit' => null,
                        'description' => 'Inventory Loss',
                    ]),
                    new JournalEntryLine([
                        'account_id' => $inventoryAccount->id,
                        'debit' => null,
                        'credit' => $totalValue,
                        'description' => 'Inventory Decreased',
                    ]),
                ];
            }
            $entry->lines()->saveMany($lines);
            if (!$entry->isBalanced()) {
                throw new \Exception('Stock adjustment journal entry is not balanced.');
            }
        });
    }
}
