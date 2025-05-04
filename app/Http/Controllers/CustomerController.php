<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use Exception;

class CustomerController extends Controller
{
    public function create()
    {
        $allCustomers = \App\Models\Customer::orderByDesc('created_at')->get();
        return view('customers.create', compact('allCustomers'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric',
            'opening_type' => 'required|in:debit,credit',
        ]);
        DB::beginTransaction();
        try {
            $customer = Customer::findOrFail($id);
            // Optionally, update the customer's ChartOfAccount name if the name changes
            if ($customer->account) {
                $customer->account->update(['name' => 'A/R - ' . $validated['name']]);
            }
            $customer->update($validated);

            // Update related journal entry lines for opening balance
            $journal = \App\Models\JournalEntry::where('reference_type', 'customer')->where('reference_id', $customer->id)->first();
            if ($journal) {
                // Get chart of accounts
                $accountsReceivable = \App\Models\ChartOfAccount::where('name', 'Accounts Receivable (Customers)')->first();
                $openingEquity = \App\Models\ChartOfAccount::where('name', 'Opening Balance Equity')->first();
                $amount = isset($validated['opening_balance']) ? (float) $validated['opening_balance'] : 0.0;
                $type = $validated['opening_type'];
                // Remove old lines
                $journal->lines()->delete();
                // Add new lines
                if ($type === 'debit') {
                    $journal->lines()->createMany([
                        [
                            'account_id' => $customer->account_id,
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
                } else {
                    $journal->lines()->createMany([
                        [
                            'account_id' => $customer->account_id,
                            'debit' => null,
                            'credit' => $amount,
                            'description' => 'Opening balance',
                            'created_at' => '2025-01-01 00:00:00',
                            'updated_at' => '2025-01-01 00:00:00',
                        ],
                        [
                            'account_id' => $openingEquity->id,
                            'debit' => $amount,
                            'credit' => null,
                            'description' => 'Opening balance equity',
                            'created_at' => '2025-01-01 00:00:00',
                            'updated_at' => '2025-01-01 00:00:00',
                        ],
                    ]);
                }
            }
            DB::commit();
            return back()->with('success', 'Customer updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'opening_balance' => 'nullable|numeric',
            'opening_type' => 'required|in:debit,credit',
        ]);
        DB::beginTransaction();
        try {
            // Create a dedicated ChartOfAccount for this customer
            $parentAccount = ChartOfAccount::where('name', 'Accounts Receivable (Customers)')->first();
            if (!$parentAccount) {
                throw new Exception('Parent account "Accounts Receivable (Customers)" not found.');
            }
            $customerAccount = ChartOfAccount::create([
                'name' => 'A/R - ' . $validated['name'],
                'code' => 'AR-' . strtoupper(uniqid()),
                'type' => 'Asset',
                'parent_id' => $parentAccount->id,
                'is_group' => false,
                'opening_balance' => 0,
            ]);
            $customer = new Customer();
            $customer->name = $validated['name'];
            $customer->phone = $validated['phone'] ?? null;
            $customer->opening_balance = $validated['opening_balance'] ?? 0;
            $customer->opening_type = $validated['opening_type'] ?? 'debit';
            $customer->account_id = $customerAccount->id;
            $customer->created_at = '2025-01-01 00:00:00';
            $customer->updated_at = '2025-01-01 00:00:00';
            $customer->save();
            // Always create journal entry, even if opening_balance is 0 or not provided
            $amount = isset($validated['opening_balance']) ? (float) $validated['opening_balance'] : 0.0;
            $type = $validated['opening_type'];
            $accountsReceivable = ChartOfAccount::where('name', 'Accounts Receivable (Customers)')->first();
            $openingEquity = ChartOfAccount::where('name', 'Opening Balance Equity')->first();
            if (!$accountsReceivable || !$openingEquity) {
                throw new Exception('Required accounts not found in chart of accounts.');
            }
            $journal = new JournalEntry();
            // Generate next auto-incrementing entry number as integer
            $lastEntry = JournalEntry::orderByRaw('CAST(entry_number AS UNSIGNED) DESC')->first();
            $nextEntryNumber = $lastEntry ? ((int)$lastEntry->entry_number + 1) : 1;
            $journal->entry_number = (string)$nextEntryNumber;
            $journal->date = '2025-01-01';
            $journal->description = 'Opening balance for customer: ' . $customer->name;
            $journal->reference_type = 'customer';
            $journal->reference_id = $customer->id;
            $journal->created_by = auth()->id();
            $journal->created_at = '2025-01-01 00:00:00';
            $journal->updated_at = '2025-01-01 00:00:00';
            $journal->save();
            if ($type === 'debit') {
                $journal->lines()->createMany([
                    [
                        'account_id' => $customer->account_id,
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
            } else {
                $journal->lines()->createMany([
                    [
                        'account_id' => $customer->account_id,
                        'debit' => null,
                        'credit' => $amount,
                        'description' => 'Opening balance',
                    ],
                    [
                        'account_id' => $openingEquity->id,
                        'debit' => $amount,
                        'credit' => null,
                        'description' => 'Opening balance equity',
                    ],
                ]);
            }
            DB::commit();
            return back()->with('success', 'Customer created successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}
