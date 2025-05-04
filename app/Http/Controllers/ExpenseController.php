<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class ExpenseController extends Controller {
    public function create() {
        $expense_accounts = \App\Models\ExpenseAccount::orderBy('name')->get();
        $banks = \App\Models\Bank::orderBy('name')->get();
        $expenses = \App\Models\Expense::with(['expenseAccount', 'paymentAccount'])->orderByDesc('date')->paginate(10);
        return view('expenses.create', compact('expense_accounts', 'banks', 'expenses'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'expense_account_id' => 'required|exists:chart_of_accounts,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_account_id' => 'required|exists:chart_of_accounts,id',
            
            'description' => 'required|string|max:1000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $expense = new \App\Models\Expense();
        $expense->expense_account_id = $validated['expense_account_id'];
        $expense->date = $validated['date'];
        $expense->expense_date = $validated['date']; // keep expense_date in sync
        $expense->amount = $validated['amount'];
        $expense->payment_account_id = $validated['payment_account_id'];
        $expense->payment_method = 'Bank';
        
        $expense->description = $validated['description'] ?? null;
        if ($request->hasFile('attachment')) {
            $expense->attachment = $request->file('attachment')->store('expenses', 'public');
        }
        $expense->save();
        // Always create a journal entry, even if amount is zero
        $amount = isset($validated['amount']) ? (float) $validated['amount'] : 0.0;
        $expenseAccount = \App\Models\ChartOfAccount::find($validated['expense_account_id']);
        $bankAccount = \App\Models\ChartOfAccount::find($validated['payment_account_id']);
        if (!$expenseAccount || !$bankAccount) {
            return back()->withErrors(['error' => 'Required accounts not found in chart of accounts.'])->withInput();
        }
        // Generate unique entry_number like EXP-000001
        $lastEntry = \App\Models\JournalEntry::where('entry_number', 'like', 'EXP-%')->orderByDesc('id')->first();
        if ($lastEntry && preg_match('/EXP-(\d+)/', $lastEntry->entry_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }
        $entryNumber = 'EXP-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        $journal = new \App\Models\JournalEntry();
        $journal->date = $expense->date;
        $journal->description = $validated['description'] ?? '';
        $journal->reference_type = 'expense';
        $journal->reference_id = $expense->id;
        $journal->entry_number = $entryNumber;
        $journal->created_by = auth()->id() ?? 1;
        $journal->save();
        $journal->lines()->createMany([
            [
                'account_id' => $expenseAccount->id,
                'debit' => $amount,
                'credit' => null,
                'description' => 'Expense',
            ],
            [
                'account_id' => $bankAccount->id,
                'debit' => null,
                'credit' => $amount,
                'description' => 'Bank/Cash',
            ],
        ]);
        return redirect()->route('expenses.create')->with('success', 'Expense recorded successfully!');
    }

    public function destroy($id)
    {
        $expense = \App\Models\Expense::findOrFail($id);
        // Delete related journal entry and lines
        $journal = \App\Models\JournalEntry::where('reference_type', 'expense')
                    ->where('reference_id', $expense->id)
                    ->first();
        if ($journal) {
            $journal->lines()->delete();
            $journal->delete();
        }
        $expense->delete();
        return redirect()->back()->with('success', 'Expense deleted successfully!');
    }
}
