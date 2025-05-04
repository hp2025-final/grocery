<?php

namespace App\Observers;

use App\Models\Expense;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

class ExpenseObserver
{
    public function created(Expense $expense)
    {
        // (Journal entry creation moved to ExpenseController@store for clarity and to prevent duplicate or missing entry_numbers.)
        // You can add additional logic here if needed, but do not create journal entries here.
    
        }
    }

