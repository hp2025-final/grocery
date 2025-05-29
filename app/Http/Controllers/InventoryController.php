<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use Exception;
class InventoryController extends Controller {
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:inventory_categories,id',
            'unit' => 'required|string|max:20',
            'buy_price' => 'required|numeric|min:0.01',
            'sale_price' => 'required|numeric|min:0.01',
            'opening_qty' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        DB::beginTransaction();
        try {
            $product = Inventory::findOrFail($id);
            $product->update($validated);

            // Update related journal entry lines for opening balance
            $journal = JournalEntry::where('reference_type', 'inventory')->where('reference_id', $product->id)->first();
            if ($journal) {
                $inventoryAccount = ChartOfAccount::where('name', 'Inventory')->first();
                $openingEquity = ChartOfAccount::where('name', 'Opening Balance Equity')->first();
                $qty = isset($validated['opening_qty']) ? (float) $validated['opening_qty'] : 0.0;
                $amount = $qty * $validated['buy_price'];
                // Remove old lines
                $journal->lines()->delete();
                // Add new lines
                $journal->lines()->createMany([
    [
        'account_id' => $inventoryAccount->id,
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
            return back()->with('success', 'Product updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function index() { return view('modules.inventory'); }

    public function create()
    {
        $last = Inventory::orderBy('id', 'desc')->first();
        $nextNumber = $last ? intval(substr($last->inventory_code, 4)) + 1 : 1;
        $nextCode = 'PRD-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        $categories = InventoryCategory::orderBy('name')->get();
        $units = ['KG', 'Pcs', 'Pack'];
        $accounts = ChartOfAccount::where('type', 'Asset')->orderBy('name')->get();
        $allProducts = Inventory::with('category')->orderBy('id', 'desc')->get();
        return view('inventory.create', compact('nextCode', 'categories', 'units', 'accounts', 'allProducts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:inventory_categories,id',
            'unit' => 'required|string|max:20',
            'buy_price' => 'required|numeric|min:0.01',
            'sale_price' => 'required|numeric|min:0.01',
            'opening_qty' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        \Log::info('InventoryController@store: Start', ['request' => $request->all()]);
        DB::beginTransaction();
        try {
            \Log::info('InventoryController@store: Creating Inventory', ['validated' => $validated]);
            $inventoryAccount = ChartOfAccount::where('name', 'Inventory')->first();
            if (!$inventoryAccount) {
                throw new Exception('Inventory account not found in chart of accounts.');
            }
            $lastProduct = Inventory::orderBy('id', 'desc')->first();
            $nextNumber = $lastProduct ? intval(substr($lastProduct->inventory_code, 4)) + 1 : 1;
            $product = Inventory::create([
                'inventory_code' => 'PRD-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT),
                'name' => $validated['name'],
                'category_id' => $validated['category_id'],
                'unit' => $validated['unit'],
                'buy_price' => $validated['buy_price'],
                'sale_price' => $validated['sale_price'],
                'opening_qty' => $validated['opening_qty'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'account_id' => $inventoryAccount->id,
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00',
            ]);
            \Log::info('InventoryController@store: Inventory saved', ['inventory_id' => $product->id]);
            // Opening balance logic
            // Always create a journal entry, even if opening_qty is zero or not provided
            $qty = isset($validated['opening_qty']) ? (float) $validated['opening_qty'] : 0.0;
            $amount = $qty * $validated['buy_price'];
            \Log::info('InventoryController@store: Opening qty and amount', ['qty' => $qty, 'amount' => $amount]);
            $inventoryAccount = ChartOfAccount::where('name', 'Inventory')->first();
            $openingEquity = ChartOfAccount::where('name', 'Opening Balance Equity')->first();
            \Log::info('InventoryController@store: ChartOfAccount lookup', ['inventoryAccount' => $inventoryAccount, 'openingEquity' => $openingEquity]);
            if (!$inventoryAccount || !$openingEquity) {
                throw new Exception('Required accounts not found in chart of accounts.');
            }
            $lastEntry = JournalEntry::orderByRaw('CAST(entry_number AS UNSIGNED) DESC')->first();
            $nextEntryNumber = $lastEntry ? ((int)$lastEntry->entry_number + 1) : 1;
            $journal = new JournalEntry();
            $journal->entry_number = $nextEntryNumber;
            $journal->date = '2025-01-01';
            $journal->description = 'Opening balance for inventory: ' . $product->name;
            $journal->reference_type = 'inventory';
            $journal->reference_id = $product->id;
            $journal->created_by = auth()->id() ?? 1;
            $journal->created_at = '2025-01-01 00:00:00';
            $journal->updated_at = '2025-01-01 00:00:00';
            $journal->save();
            \Log::info('InventoryController@store: JournalEntry created', ['journal_id' => $journal->id]);
            $journal->lines()->createMany([
                [
                    'account_id' => $product->account_id,
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
            \Log::info('InventoryController@store: JournalEntry lines created');
            DB::commit();
            \Log::info('InventoryController@store: Success');
            return back()->with('success', 'Product created successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('InventoryController@store: Exception', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            // Find the inventory item
            $inventory = Inventory::findOrFail($id);
            
            // Delete related journal entry lines
            $journal = JournalEntry::where('reference_type', 'inventory')
                                 ->where('reference_id', $id)
                                 ->first();
            
            if ($journal) {
                // Delete journal entry lines first
                $journal->lines()->delete();
                // Delete the journal entry
                $journal->delete();
            }
            
            // Delete the inventory item
            $inventory->delete();
            
            DB::commit();
            return back()->with('success', 'Product deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete product. ' . $e->getMessage()]);
        }
    }
}

