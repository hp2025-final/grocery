<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use Exception;

class BankController extends Controller
{
    public function index()
    {
        $banks = \App\Models\Bank::all();
        return view('banks.index', compact('banks'));
    }
    public function create()
    {
        $accounts = ChartOfAccount::where('type', 'Asset')->orderBy('name')->get();
        $banks = \App\Models\Bank::orderBy('created_at', 'desc')->get();
        return view('banks.create', compact('accounts', 'banks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'branch' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'account_title' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Create or get ChartOfAccount for this bank
            $account = \App\Models\ChartOfAccount::firstOrCreate(
                [
                    'name' => $validated['name'],
                    'type' => 'Asset'
                ],
                [
                    'code' => 'BANK-' . str_pad((\App\Models\ChartOfAccount::where('type', 'Asset')->where('code', 'like', 'BANK-%')->count() + 1), 4, '0', STR_PAD_LEFT),
                    'description' => 'Bank Account: ' . $validated['name'],
                    'parent_id' => 2,
                    'created_at' => '2025-01-01 00:00:00',
                    'updated_at' => '2025-01-01 00:00:00',
                ]
            );
            // Create the bank and link the account
            $bank = \App\Models\Bank::create([
                'name' => $validated['name'],
                'branch' => $validated['branch'],
                'account_number' => $validated['account_number'],
                'account_title' => $validated['account_title'],
                'iban' => $validated['iban'],
                'swift_code' => $validated['swift_code'],
                'opening_balance' => $validated['opening_balance'],
                'notes' => $validated['notes'],
                'account_id' => $account->id,
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00',
            ]);
            // Create opening balance journal entry if needed
            $amount = isset($validated['opening_balance']) ? (float) $validated['opening_balance'] : 0.0;
            $openingEquity = ChartOfAccount::where('name', 'Opening Balance Equity')->first();
            if (!$openingEquity) {
                throw new Exception('Required account Opening Balance Equity not found in chart of accounts.');
            }
            $journal = new JournalEntry();
            $journal->entry_number = 'JE-' . str_pad((JournalEntry::max('id') + 1), 6, '0', STR_PAD_LEFT);
            $journal->date = '2025-01-01';
            $journal->description = 'Bank opening balance';
            $journal->reference_type = 'bank';
            $journal->reference_id = $bank->id;
            $journal->created_by = auth()->id() ?? 1;
            $journal->created_at = '2025-01-01 00:00:00';
            $journal->updated_at = '2025-01-01 00:00:00';
            $journal->save();
            $journal->lines()->createMany([
                [
                    'account_id' => $account->id,
                    'debit' => $amount,
                    'credit' => null,
                    'description' => 'Opening balance',
                    'created_at' => '2025-01-01 00:00:00',
                    'updated_at' => '2025-01-01 00:00:00',
                ],
                [
                    'account_id' => $openingEquity->id,
                    'debit' => null,
                    'credit' => $amount,
                    'description' => 'Opening balance equity',
                    'created_at' => '2025-01-01 00:00:00',
                    'updated_at' => '2025-01-01 00:00:00',
                ],
            ]);
            DB::commit();
            $accounts = \App\Models\ChartOfAccount::where('type', 'Asset')->orderBy('name')->get();
            $banks = \App\Models\Bank::orderBy('created_at', 'desc')->get();
            return view('banks.create', compact('accounts', 'banks'))
                ->with('success', 'Bank created successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $bank = \App\Models\Bank::findOrFail($id);
        $accounts = \App\Models\ChartOfAccount::where('type', 'Asset')->orderBy('name')->get();
        return view('banks.edit', compact('bank', 'accounts'));
    }

    public function destroy($id)
    {
        $bank = \App\Models\Bank::findOrFail($id);
        $bank->delete();
        return back()->with('success', 'Bank deleted successfully!');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'branch' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'account_title' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);
        \DB::beginTransaction();
        try {
            $bank = \App\Models\Bank::findOrFail($id);
            $bank->update($validated);
            // Update related ChartOfAccount
            $account = $bank->account_id ? \App\Models\ChartOfAccount::find($bank->account_id) : null;
            if ($account) {
                $account->update([
                    'name' => $validated['name'],
                    'account_number' => $validated['account_number'],
                    'account_title' => $validated['account_title'],
                ]);
            }
            // Update journal_entries and journal_entry_lines if opening_balance is changed
            if (isset($validated['opening_balance'])) {
                $journal = \App\Models\JournalEntry::where('reference_type', 'bank')
                    ->where('reference_id', $bank->id)
                    ->first();
                if ($journal) {
                    $amount = (float)($validated['opening_balance'] ?? 0);
                    // Update bank account line (debit)
                    $bankLine = $journal->lines()->where('account_id', $account ? $account->id : null)->first();
                    if ($bankLine) {
                        $bankLine->update([
                            'debit' => $amount,
                            'credit' => null,
                            'description' => 'Opening balance',
                            'updated_at' => now(),
                        ]);
                    }
                    // Update Opening Balance Equity line (credit)
                    $openingEquity = \App\Models\ChartOfAccount::where('name', 'Opening Balance Equity')->first();
                    if ($openingEquity) {
                        $equityLine = $journal->lines()->where('account_id', $openingEquity->id)->first();
                        if ($equityLine) {
                            $equityLine->update([
                                'debit' => null,
                                'credit' => $amount,
                                'description' => 'Opening balance equity',
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }
            \DB::commit();
            return redirect()->route('banks.create')->with('success', 'Bank updated successfully!');
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}
