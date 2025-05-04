<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sale;
use App\Models\JournalEntry;

class FixSaleJournalReferences extends Command
{
    protected $signature = 'fix:sale-journal-references';
    protected $description = 'Fix missing reference_type and reference_id for sales journal entries';

    public function handle()
    {
        $sales = Sale::orderBy('id')->get();
        $journalEntries = JournalEntry::where('entry_number', 'like', 'INV-%')
            ->where(function($q) {
                $q->whereNull('reference_type')->orWhereNull('reference_id');
            })
            ->orderBy('id')->get();

        $count = 0;
        foreach ($journalEntries as $i => $entry) {
            $sale = $sales[$i] ?? null;
            if ($sale) {
                $entry->reference_type = 'Sale';
                $entry->reference_id = $sale->id;
                $entry->save();
                $this->info("Updated entry {$entry->id} with sale {$sale->id}");
                $count++;
            }
        }
        $this->info("Done. Updated $count journal entries.");
    }
}
