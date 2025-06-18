<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\Sale;

class SaleController extends Controller {
    public function create(Request $request) {
        $customers = \App\Models\Customer::orderBy('name')->get();
        $products = \App\Models\Inventory::orderBy('name')->get()->map(function($p) {
    return [
        'id' => $p->id,
        'name' => $p->name,
        'unit_name' => $p->unit,
        'sale_price' => $p->sale_price,
    ];
})->values()->toArray();
        $search = $request->input('search');
        $salesQuery = \App\Models\Sale::with(['customer', 'items.product', 'items.unit'])
            ->orderByDesc('sale_date')
            ->take(50); // Limit to last 50 invoices
        if ($search) {
            $salesQuery->where(function($q) use ($search) {
                $q->where('sale_number', 'like', "%$search%")
                  ->orWhereHas('customer', function($qc) use ($search) {
                      $qc->where('name', 'like', "%$search%");
                  });
            });
        }
        $sales = $salesQuery->paginate(10)->withQueryString();
        return view('sales.create', compact('customers', 'products', 'sales', 'search'));
    }

    public function edit($id) {
        $sale = \App\Models\Sale::with(['customer', 'items.product', 'items.unit'])->findOrFail($id);
        $customers = \App\Models\Customer::orderBy('name')->get();
        $products = \App\Models\Inventory::orderBy('name')->get()->map(function($p) {
    return [
        'id' => $p->id,
        'name' => $p->name,
        'unit_name' => $p->unit,
        'sale_price' => $p->sale_price,
    ];
})->values()->toArray();

        // Add paginated sales list limited to last 50 records
        $search = request()->input('search');
        $salesQuery = \App\Models\Sale::with(['customer', 'items.product', 'items.unit'])
            ->orderByDesc('sale_date')
            ->take(50); // Limit to last 50 invoices
        if ($search) {
            $salesQuery->where(function($q) use ($search) {
                $q->where('sale_number', 'like', "%$search%")
                  ->orWhereHas('customer', function($qc) use ($search) {
                      $qc->where('name', 'like', "%$search%");
                  });
            });
        }
        $sales = $salesQuery->paginate(10)->withQueryString();

        // Map the sale items to match the expected format
        $saleItems = $sale->items->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->rate,
                'unit_name' => $item->unit->name ?? '',
                'total' => $item->total_amount
            ];
        })->toArray();

        return view('sales.edit', compact('sale', 'customers', 'products', 'sales', 'search', 'saleItems'));
    }

    public function destroy($id) {
        $sale = \App\Models\Sale::findOrFail($id);
        // Delete all sale items
        $sale->items()->delete();
        // Find and delete related journal entries and their lines
        $journalEntries = \App\Models\JournalEntry::where('reference_type', 'sale')->where('reference_id', $sale->id)->get();
        foreach ($journalEntries as $entry) {
            $entry->lines()->delete();
            $entry->delete();
        }
        $sale->delete();
        return back()->with('success', 'Sale and related records deleted successfully!');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'sale_date' => 'required|date',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:inventories,id',
            'products.*.quantity' => 'required|numeric|min:0.01',
            'products.*.unit_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'required|string|max:1000'
        ]);

        try {
            \DB::beginTransaction();

            $sale = new Sale();
            $sale->customer_id = $validated['customer_id'];
            $sale->sale_date = $validated['sale_date'];
            
            // Generate unique sale_number
            $lastSale = Sale::orderByDesc('id')->first();
            $nextNumber = $lastSale ? ((int)substr($lastSale->sale_number, 4)) + 1 : 1;
            $sale->sale_number = 'SAL-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
            
            $sale->discount_amount = $validated['discount'] ?? 0;
            $sale->notes = $validated['notes'] ?? null;
            $sale->payment_status = 'Unpaid'; // Default status
            $sale->total_amount = 0;
            $sale->net_amount = 0;
            $sale->save();

        $subtotal = 0;
        foreach ($validated['products'] as $item) {
            $inventory = \App\Models\Inventory::query()->where('id', $item['product_id'])->first();
            $lineTotal = $item['quantity'] * $item['unit_price'];
            // Find unit_id from units table
            $unitId = null;
            if ($inventory && $inventory->unit) {
                $unitModel = \App\Models\Unit::firstOrCreate(
                    ['name' => $inventory->unit],
                    ['abbreviation' => $inventory->unit]
                );
                $unitId = $unitModel->id;
            }
            // Save sale item using inventory id as product_id
            \App\Models\SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $item['product_id'], // inventory id
                'quantity' => $item['quantity'],
                'unit_id' => $unitId,
                'rate' => $item['unit_price'],
                'total_amount' => $lineTotal,
            ]);
            $subtotal += $lineTotal;
        }
            
        $sale->total_amount = $subtotal;
        $sale->net_amount = $subtotal - ($validated['discount'] ?? 0);
        $sale->save();

            // --- Journal Entry Creation Logic with Better Error Handling ---
            Log::info('Starting journal entry creation for sale', ['sale_id' => $sale->id]);

            // Get required accounts with error checking
            $salesIncome = ChartOfAccount::where('type', 'Income')
                ->where(function($q) {
                    $q->where('code', '4001')
                      ->orWhere('name', 'Sales')
                      ->orWhere('name', 'Sales Revenue');
                })->first();

            if (!$salesIncome) {
                Log::error('Sales Income account not found');
                throw new \Exception('Sales Income account not found in chart of accounts');
            }

            $discountAllowed = null;
            if ($sale->discount_amount > 0) {
                $discountAllowed = ChartOfAccount::where('type', 'Expense')
                    ->where('name', 'Discount Allowed')
                    ->first();
                
                if (!$discountAllowed) {
                    Log::error('Discount Allowed account not found');
                    throw new \Exception('Discount Allowed account not found in chart of accounts');
                }
            }

            $customer = \App\Models\Customer::with('account')->find($sale->customer_id);
            if (!$customer || !$customer->account) {
                Log::error('Customer or customer account not found', [
                    'customer_id' => $sale->customer_id,
                    'customer_exists' => (bool)$customer,
                    'account_exists' => $customer ? (bool)$customer->account : false
                    ]);
                throw new \Exception('Customer account not found');
                }

            // Create journal entry
            $last = JournalEntry::where('entry_number', 'like', 'INV-%')->orderByDesc('id')->first();
                $nextNum = $last ? (intval(substr($last->entry_number, 4)) + 1) : 1;
                $entryNumber = 'INV-' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);

            $entry = new JournalEntry([
                    'entry_number' => $entryNumber,
                'date' => $sale->sale_date,
                'description' => 'Sale Invoice #' . $sale->sale_number,
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                'created_by' => auth()->id() ?? 1
                ]);

            Log::info('Created journal entry', ['entry_number' => $entryNumber]);

                $entry->save();

            // Create journal entry lines
            $lines = [];
            
            // Credit Sales Income
            $lines[] = [
                        'account_id' => $salesIncome->id,
                        'debit' => null,
                'credit' => $sale->total_amount,
                'description' => 'Sales Income'
                ];

            // Debit Customer Account
            $lines[] = [
                'account_id' => $customer->account->id,
                'debit' => $sale->net_amount,
                        'credit' => null,
                'description' => 'Accounts Receivable'
            ];

            // Debit Discount Allowed (if any)
            if ($sale->discount_amount > 0 && $discountAllowed) {
                $lines[] = [
                    'account_id' => $discountAllowed->id,
                    'debit' => $sale->discount_amount,
                        'credit' => null,
                    'description' => 'Discount Allowed'
                ];
            }

            // --- COGS and Inventory Journal Entries ---
            $cogsAccount = \App\Models\ChartOfAccount::where('name', 'Cost of Goods Sold')->first();
            $inventoryAccount = \App\Models\ChartOfAccount::where('name', 'Inventory')->first();
            $totalCOGS = 0;
            foreach ($sale->items as $item) {
                $product = \App\Models\Inventory::find($item->product_id);
                $cost = $product ? $product->buy_price : 0;
                $quantity = $item->quantity;
                $totalCOGS += $cost * $quantity;
            }
            if ($cogsAccount && $inventoryAccount && $totalCOGS > 0) {
                $lines[] = [
                    'account_id' => $cogsAccount->id,
                    'debit' => $totalCOGS,
                    'credit' => null,
                    'description' => 'COGS for Sale #' . $sale->sale_number
                ];
                $lines[] = [
                    'account_id' => $inventoryAccount->id,
                    'debit' => null,
                    'credit' => $totalCOGS,
                    'description' => 'Inventory reduction for Sale #' . $sale->sale_number
                ];
            }
            // --- END COGS and Inventory Journal Entries ---

            foreach ($lines as $line) {
                $entry->lines()->create($line);
            }

            Log::info('Created journal entry lines', [
                'entry_id' => $entry->id,
                'line_count' => count($lines)
            ]);

            \DB::commit();
            return redirect()->back()->with('success', 'Sale created successfully!');

        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Failed to create sale and journal entry', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Failed to create sale: ' . $e->getMessage()])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'sale_date' => 'required|date',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:inventories,id',
            'products.*.quantity' => 'required|numeric|min:0.01',
            'products.*.unit_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'required|string|max:1000',
            'payment_status' => 'required|in:Paid,Unpaid,Partial'
        ]);

        $sale = \App\Models\Sale::findOrFail($id);
        $sale->customer_id = $validated['customer_id'];
        $sale->sale_date = $validated['sale_date'];
        $sale->discount_amount = $validated['discount'] ?? 0;
        $sale->notes = $validated['notes'] ?? null;
        $sale->payment_status = $validated['payment_status'];
        $sale->save();

        // Remove old items
        $sale->items()->delete();
        $subtotal = 0;
        foreach ($validated['products'] as $item) {
            $inventory = \App\Models\Inventory::query()->where('id', $item['product_id'])->first();
            $lineTotal = $item['quantity'] * $item['unit_price'];
            $unitId = null;
            if ($inventory && $inventory->unit) {
                $unitModel = \App\Models\Unit::firstOrCreate(
                    ['name' => $inventory->unit],
                    ['abbreviation' => $inventory->unit]
                );
                $unitId = $unitModel->id;
            }
            // Ensure product category exists and get its ID
            $productCategoryId = null;
            if ($inventory->category_id) {
                $inventoryCategory = \App\Models\InventoryCategory::find($inventory->category_id);
                if ($inventoryCategory) {
                    $productCategory = \App\Models\ProductCategory::firstOrCreate(
                        ['name' => $inventoryCategory->name],
                        ['description' => $inventoryCategory->name]
                    );
                    $productCategoryId = $productCategory->id;
                }
            }
            $productModel = \App\Models\Product::firstOrCreate([
                'name' => $inventory->name,
                'category_id' => $productCategoryId,
                'unit_id' => $unitId
            ]);
            $sale->items()->create([
                'product_id' => $productModel->id,
                'quantity' => $item['quantity'],
                'unit_id' => $unitId,
                'rate' => $item['unit_price'],
                'total_amount' => $lineTotal,
            ]);
            $subtotal += $lineTotal;
        }
        $sale->total_amount = $subtotal;
        $sale->net_amount = $subtotal - ($sale->discount_amount ?? 0);
        $sale->save();

        // --- Update Journal Entries ---
        \DB::transaction(function () use ($sale) {
            // Delete old journal entries and lines for this sale
            $journalEntries = \App\Models\JournalEntry::where('reference_type', 'sale')->where('reference_id', $sale->id)->get();
            foreach ($journalEntries as $entry) {
                $entry->lines()->delete();
                $entry->delete();
            }
            // Recreate journal entry (same logic as store)
            $amount = $sale->total_amount;
            $discount = $sale->discount_amount ?? 0;
            $net = $sale->net_amount;
            // Get relevant accounts
            $salesIncome = \App\Models\ChartOfAccount::where('type', 'Income')->where(function($q) {
                $q->where('code', '4001')->orWhere('name', 'Sales')->orWhere('name', 'Sales Revenue');
            })->first();
            $discountAllowed = \App\Models\ChartOfAccount::where('type', 'Expense')->where(function($q) {
                $q->where('name', 'Discount Allowed');
            })->first();
            $customerModel = \App\Models\Customer::find($sale->customer_id);
            $customerAccount = $customerModel ? $customerModel->account : null;
            if (!$salesIncome || !$customerAccount) {
                \Log::error('SaleController: Required account(s) not found for sales journal entry.', [
                    'salesIncome' => $salesIncome,
                    'customerAccount' => $customerAccount
                ]);
                throw new \Exception('Required account(s) not found for sales journal entry.');
            }
            // Generate journal entry number
            $last = \App\Models\JournalEntry::where('entry_number', 'like', 'INV-%')->orderByDesc('id')->first();
            $nextNum = $last ? (intval(substr($last->entry_number, 4)) + 1) : 1;
            $entryNumber = 'INV-' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);
            $entry = new \App\Models\JournalEntry([
                'entry_number' => $entryNumber,
                'entry_date' => $sale->sale_date,
                'date' => $sale->sale_date,
                'description' => 'Sale Invoice #' . $sale->sale_number,
                'reference_type' => 'sale',
                'reference_id' => $sale->id,
                'created_by' => auth()->id() ?? 1,
            ]);
            $entry->save();
            // Credit Sales Income
            $entry->lines()->create([
                'account_id' => $salesIncome->id,
                'credit' => $amount,
                'debit' => 0,
                'description' => 'Sales Income'
            ]);
            // Debit Customer
            if ($customerAccount) {
                $entry->lines()->create([
                    'account_id' => $customerAccount->id,
                    'debit' => $net,
                    'credit' => 0,
                    'description' => 'Accounts Receivable'
                ]);
            }
            // Debit Discount Allowed (if any)
            if ($discount > 0 && $discountAllowed) {
                $entry->lines()->create([
                    'account_id' => $discountAllowed->id,
                    'debit' => $discount,
                    'credit' => 0,
                    'description' => 'Discount Allowed'
                ]);
            }
            // --- COGS and Inventory Journal Entries ---
            $cogsAccount = \App\Models\ChartOfAccount::where('name', 'Cost of Goods Sold')->first();
            $inventoryAccount = \App\Models\ChartOfAccount::where('name', 'Inventory')->first();
            $totalCOGS = 0;
            foreach ($sale->items as $item) {
                $product = \App\Models\Inventory::find($item->product_id);
                $cost = $product ? $product->buy_price : 0;
                $quantity = $item->quantity;
                $totalCOGS += $cost * $quantity;
            }
            if ($cogsAccount && $inventoryAccount && $totalCOGS > 0) {
                $entry->lines()->create([
                    'account_id' => $cogsAccount->id,
                    'debit' => $totalCOGS,
                    'credit' => null,
                    'description' => 'COGS for Sale #' . $sale->sale_number
                ]);
                $entry->lines()->create([
                    'account_id' => $inventoryAccount->id,
                    'debit' => null,
                    'credit' => $totalCOGS,
                    'description' => 'Inventory reduction for Sale #' . $sale->sale_number
                ]);
            }
            // --- END COGS and Inventory Journal Entries ---
        });
        // --- End Update Journal Entries ---

        return redirect()->route('sales.edit', $sale->id)->with('success', 'Sale updated successfully!');
    }

    public function show($id) {
        $sale = Sale::with(['customer', 'items.product', 'items.unit'])->findOrFail($id);
        return view('sales.invoice', compact('sale'));
    }
}
