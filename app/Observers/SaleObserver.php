<?php

namespace App\Observers;

use App\Models\Sale;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

class SaleObserver
{
    // Journal entry creation logic has been moved to SaleController for transactional integrity.
    public function created(Sale $sale)
    {
        // No longer used.
    }
}
