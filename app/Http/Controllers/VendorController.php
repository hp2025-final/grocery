<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Vendor;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use Exception;

class VendorController extends Controller
{
    public function index()
    {
        $allVendors = \App\Models\Vendor::orderByDesc('created_at')->get();
        return view('vendors.index', compact('allVendors'));
    }
    public function create()
    {
        $allVendors = \App\Models\Vendor::orderByDesc('created_at')->get();
        return view('vendors.create', compact('allVendors'));
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
            // Create Chart of Account for this vendor
            $parentAccount = ChartOfAccount::where('name', 'Accounts Payable (Vendors)')->first();
            if (!$parentAccount) {
                throw new Exception('Accounts Payable (Vendors) account not found in chart of accounts.');
            }
            $vendorAccount = ChartOfAccount::create([
                'name' => 'A/P - ' . $validated['name'],
                'code' => 'AP-' . strtoupper(uniqid()),
                'type' => 'Liability',
                'parent_id' => $parentAccount->id,
                'is_group' => false,
                'opening_balance' => 0,
            ]);
            $vendor = new Vendor();
            $vendor->name = $validated['name'];
            $vendor->phone = $validated['phone'] ?? null;
            $vendor->opening_balance = $validated['opening_balance'] ?? 0;
            $vendor->opening_type = $validated['opening_type'] ?? 'credit';
            $vendor->account_id = $vendorAccount->id;
            $vendor->created_at = '2025-01-01 00:00:00';
            $vendor->updated_at = '2025-01-01 00:00:00';
            $vendor->save();
            // Always create journal entry, even if opening_balance is 0 or not provided
            $amount = isset($validated['opening_balance']) ? (float) $validated['opening_balance'] : 0.0;
            $type = $validated['opening_type'];
            $accountsPayable = ChartOfAccount::where('name', 'Accounts Payable (Vendors)')->first();
            $openingEquity = ChartOfAccount::where('name', 'Opening Balance Equity')->first();
            if (!$accountsPayable || !$openingEquity) {
                throw new Exception('Required accounts not found in chart of accounts.');
            }
            $journal = new JournalEntry();
            $journal->date = '2025-01-01 00:00:00';
            $journal->description = 'Opening balance for vendor: ' . $vendor->name;
            $journal->reference_type = 'vendor';
            $journal->reference_id = $vendor->id;
            $journal->created_by = auth()->id();
            $journal->created_at = '2025-01-01 00:00:00';
            $journal->updated_at = '2025-01-01 00:00:00';
            // Generate JVV-00001 style entry_number
            $lastEntry = JournalEntry::where('entry_number', 'like', 'JVV-%')->orderByDesc('id')->first();
            if ($lastEntry && preg_match('/JVV-(\d+)/', $lastEntry->entry_number, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            } else {
                $nextNumber = 1;
            }
            $journal->entry_number = 'JVV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            $journal->save();
            if ($type === 'credit') {
                $journal->lines()->createMany([
                    [
                        'account_id' => $vendor->account_id,
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
            } else {
                $journal->lines()->createMany([
                    [
                        'account_id' => $vendor->account_id,
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
            }
            DB::commit();
            return redirect()->route('vendors.create')->with('success', 'Vendor created successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $vendor = Vendor::findOrFail($id);
        $allVendors = Vendor::orderByDesc('created_at')->get();
        return view('vendors.edit', compact('vendor', 'allVendors'));
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
            $vendor = Vendor::findOrFail($id);
            // Optionally update vendor's account name if name changes
            if ($vendor->account) {
                $vendor->account->update(['name' => 'A/P - ' . $validated['name']]);
            }
            $vendor->update($validated);

            // Update or create journal entry for opening balance
            $amount = isset($validated['opening_balance']) ? (float) $validated['opening_balance'] : 0.0;
            $type = $validated['opening_type'];
            $accountsPayable = ChartOfAccount::where('name', 'Accounts Payable (Vendors)')->first();
            $openingEquity = ChartOfAccount::where('name', 'Opening Balance Equity')->first();
            if (!$accountsPayable || !$openingEquity) {
                throw new Exception('Required accounts not found in chart of accounts.');
            }
            $journal = JournalEntry::where('reference_type', 'vendor')->where('reference_id', $vendor->id)->first();
            if ($journal) {
                $journal->description = 'Opening balance for vendor: ' . $vendor->name;
                $journal->date = '2025-01-01 00:00:00';
                $journal->created_at = '2025-01-01 00:00:00';
                $journal->updated_at = '2025-01-01 00:00:00';
                $journal->save();
                // Remove old lines and create new
                $journal->lines()->delete();
                // Add new lines
                if ($type === 'credit') {
                    $journal->lines()->createMany([
                        [
                            'account_id' => $vendor->account_id,
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
                } else {
                    $journal->lines()->createMany([
                        [
                            'account_id' => $vendor->account_id,
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
                }
            }
            DB::commit();
            return redirect()->route('vendors.create')->with('success', 'Vendor updated successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}