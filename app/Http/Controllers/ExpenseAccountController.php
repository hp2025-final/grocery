<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use Exception;

class ExpenseAccountController extends Controller
{
    public function ledgerFilter(Request $request, $id)
    {
        $query = \App\Models\Expense::where('expense_account_id', $id);
        $openingBalance = 0;
        if ($request->filled('from')) {
            $from = $request->input('from');
            $query->where('date', '>=', $from);
            $openingBalance = \App\Models\Expense::where('expense_account_id', $id)
                ->where('date', '<', $from)
                ->sum('amount');
        }
        if ($request->filled('to')) {
            $query->where('date', '<=', $request->input('to'));
        }
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%$search%")
                  ->orWhereHas('paymentAccount', function($q2) use ($search) {
                      $q2->where('name', 'like', "%$search%") ;
                  });
            });
        }
        $expenses = $query->orderBy('date')->paginate(10);
        $html = view('expenses._expenses_table_ledger', compact('expenses', 'openingBalance'))->render();
        return response()->json(['html' => $html]);
    }
    public function ledger($id)
    {
        $account = \App\Models\ChartOfAccount::findOrFail($id);
        $expenses = \App\Models\Expense::where('expense_account_id', $id)
            ->orderBy('date')
            ->paginate(10);
        return view('expense_accounts.ledger', compact('account', 'expenses'));
    }
    public function create()
    {
        // Find the parent 'Business Expense' account
        $parent = \App\Models\ChartOfAccount::where('name', 'Business Expense')->where('type', 'Expense')->first();
        $expenseAccounts = collect();
        if ($parent) {
            $expenseAccounts = \App\Models\ChartOfAccount::where('type', 'Expense')
                ->where('parent_id', $parent->id)
                ->orderByDesc('created_at')
                ->get();
            // Optionally, add opening_balance as a dynamic attribute for display
            foreach ($expenseAccounts as $acc) {
                $acc->opening_balance = null; // Default
                $journal = \App\Models\JournalEntry::where('reference_type', 'expense_account')
                    ->where('reference_id', $acc->id)
                    ->first();
                if ($journal) {
                    $line = $journal->lines()->where('account_id', $acc->id)->first();
                    if ($line) {
                        $acc->opening_balance = $line->debit ?? 0;
                    }
                }
            }
        }
        return view('expense_accounts.create', compact('expenseAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Ensure the parent 'Business Expense' exists (id=12 if possible)
            $parent = ChartOfAccount::where('id', 12)
                ->where('name', 'Business Expense')
                ->where('type', 'Expense')
                ->first();
            if (!$parent) {
                $parent = ChartOfAccount::firstOrCreate(
                    [ 'name' => 'Business Expense', 'type' => 'Expense' ],
                    [ 'code' => 'EXP-0000', 'description' => 'All business expenses', 'parent_id' => null ]
                );
                // Optionally force id=12 if table is empty, else use whatever id is assigned
            }
            // Create the new expense account
            $account = ChartOfAccount::create([
                'name' => $validated['name'],
                'type' => 'Expense',
                'parent_id' => $parent->id,
                'code' => 'EXP-' . str_pad((ChartOfAccount::where('type', 'Expense')->count() + 1), 4, '0', STR_PAD_LEFT),
            ]);

            // Create ExpenseAccount record
            $expenseAccount = \App\Models\ExpenseAccount::create([
                'name' => $validated['name'],
                'account_id' => $account->id,
            ]);

            // Create JournalEntry and JournalEntryLines (both with 0 amount)
            $openingEquity = ChartOfAccount::where('name', 'Opening Balance Equity')->first();
            if (!$openingEquity) {
                throw new Exception('Required account Opening Balance Equity not found in chart of accounts.');
            }
            $journal = new JournalEntry();
            $journal->entry_number = 'JE-' . str_pad((JournalEntry::max('id') + 1), 6, '0', STR_PAD_LEFT);
            $journal->date = now()->toDateString();
            $journal->description = 'Expense account opening balance';
            $journal->reference_type = 'expense_account';
            $journal->reference_id = $expenseAccount->id;
            $journal->created_by = auth()->id() ?? 1;
            $journal->save();
            $journal->lines()->createMany([
                [
                    'account_id' => $account->id,
                    'debit' => 0,
                    'credit' => null,
                    'description' => 'Opening balance',
                ],
                [
                    'account_id' => $openingEquity->id,
                    'debit' => null,
                    'credit' => 0,
                    'description' => 'Opening balance equity',
                ],
            ]);

            DB::commit();
            return redirect()->route('expense-accounts.create')->with('success', 'Expense account created successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}
