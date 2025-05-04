<?php
// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
\Illuminate\Support\Facades\Artisan::call('cache:clear');

use App\Models\Sale;
use App\Models\JournalEntry;

$sales = Sale::orderBy('id')->get();
$journalEntries = JournalEntry::where('entry_number', 'like', 'INV-%')
    ->where(function($q) {
        $q->whereNull('reference_type')->orWhereNull('reference_id');
    })
    ->orderBy('id')->get();

foreach ($journalEntries as $i => $entry) {
    $sale = $sales[$i] ?? null;
    if ($sale) {
        $entry->reference_type = 'Sale';
        $entry->reference_id = $sale->id;
        $entry->save();
        echo "Updated entry {$entry->id} with sale {$sale->id}\n";
    }
}
echo "Done updating journal entries.\n";
