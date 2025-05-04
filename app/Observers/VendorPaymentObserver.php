<?php

namespace App\Observers;

use App\Models\VendorPayment;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

class VendorPaymentObserver
{
    public function created(VendorPayment $payment)
    {
        // Journal entry creation moved to VendorPaymentController to prevent duplicate entries.
        // This observer method is intentionally left blank.
        // If you need to handle additional logic on vendor payment creation, add it here, but do NOT create journal entries here.
        //
        // Reason: Having journal entry creation in both the controller and observer caused duplicate journal entries and lines.
        // All journal entry logic for vendor payments is now centralized in VendorPaymentController@store for clarity and maintainability.
    }
}
