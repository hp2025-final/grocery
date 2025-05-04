<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;

class ExpenseLedgerController extends Controller
{
    public function show($id)
    {
        $account = ChartOfAccount::findOrFail($id);
        // Fetch all journal entry lines for this expense account, ordered by date/id
        $lines = JournalEntryLine::with(['journalEntry', 'account.bank'])
            ->where('account_id', $id)
            ->orderBy('created_at')
            ->paginate(20);
        return view('expense_accounts.ledger', compact('account', 'lines'));
    }
}
