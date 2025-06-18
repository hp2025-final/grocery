<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class PurchaseController extends Controller {
    public function edit($id) {
        $purchase = \App\Models\Purchase::with(['vendor', 'items.product', 'items.unit'])->findOrFail($id);
        $vendors = \App\Models\Vendor::orderBy('name')->get();
        $products = \App\Models\Inventory::orderBy('name')->get()->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'unit_name' => $p->unit,
                'buy_price' => $p->buy_price,
            ];
        })->values()->toArray();

        // Add paginated purchases list
        $search = request()->input('search');
        $purchasesQuery = \App\Models\Purchase::with(['vendor', 'items.product', 'items.unit'])->orderByDesc('purchase_date');
        if ($search) {
            $purchasesQuery->where(function($q) use ($search) {
                $q->where('purchase_number', 'like', "%$search%")
                  ->orWhereHas('vendor', function($qc) use ($search) {
                      $qc->where('name', 'like', "%$search%");
                  });
            });
        }
        $purchases = $purchasesQuery->paginate(5)->withQueryString();

        // Map the purchase items to match the expected format
        $purchaseItems = $purchase->items->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->rate,
                'unit_name' => $item->unit->name ?? '',
                'total' => $item->amount
            ];
        })->toArray();

        return view('purchases.edit', compact('purchase', 'vendors', 'products', 'purchases', 'search', 'purchaseItems'));
    }
    public function update(Request $request, $id) {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'purchase_date' => 'required|date',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:inventories,id',
            'products.*.quantity' => 'required|numeric|min:0.01',
            'products.*.unit_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $purchase = \App\Models\Purchase::findOrFail($id);
        $purchase->vendor_id = $validated['vendor_id'];
        $purchase->purchase_date = $validated['purchase_date'];
        $purchase->discount_amount = $validated['discount'] ?? 0;
        $purchase->notes = $validated['notes'] ?? null;
        $purchase->payment_status = 'Unpaid'; // Set default payment status
        $purchase->total_amount = 0; // will update after items
        $purchase->net_amount = 0; // will update after items
        $purchase->save();

        // Delete old items
        $purchase->items()->delete();

        $subtotal = 0;
        foreach ($validated['products'] as $item) {
            $product = \App\Models\Inventory::find($item['product_id']);
            $lineTotal = $item['quantity'] * $item['unit_price'];
            if (!$product) {
                throw new \Exception('Selected inventory product not found.');
            }
            // Find or create the product category in products table
            $inventoryCategory = $product->category;
            $productCategoryId = null;
            if ($inventoryCategory) {
                $productCategory = \App\Models\ProductCategory::firstOrCreate(
                    ['name' => $inventoryCategory->name],
                    ['description' => $inventoryCategory->name]
                );
                $productCategoryId = $productCategory->id;
            }
            // Find or create the unit
            $unit = \App\Models\Unit::where('abbreviation', $product->unit)
                ->orWhere('name', $product->unit)
                ->first();
            if (!$unit) {
                throw new \Exception('Unit not found for inventory product.');
            }
            // Find or create the product in the products table
            $productModel = \App\Models\Product::firstOrCreate(
                [
                    'name' => $product->name,
                    'unit_id' => $unit->id
                ],
                [
                    'category_id' => $productCategoryId,
                    'sale_price' => $product->sale_price,
                    'buy_price' => $product->buy_price,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
            $purchaseItem = $purchase->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'rate' => $item['unit_price'],
                'unit_id' => $unit->id,
                'amount' => $lineTotal,
            ]);
            $subtotal += $lineTotal;
        }
        $purchase->total_amount = $subtotal;
        $purchase->net_amount = $subtotal - ($validated['discount'] ?? 0);
        $purchase->save();

        // --- Update Journal Entries ---
        \DB::transaction(function () use ($purchase) {
            // Delete old journal entries and lines for this purchase
            $journalEntries = \App\Models\JournalEntry::where('reference_type', 'purchase')->where('reference_id', $purchase->id)->get();
            foreach ($journalEntries as $entry) {
                $entry->lines()->delete();
                $entry->delete();
            }
            // Recreate journal entry (same logic as store)
            $amount = $purchase->total_amount;
            $discount = $purchase->discount_amount ?? 0;
            $net = $purchase->net_amount;
            // Get relevant accounts
            $inventoryAccount = \App\Models\ChartOfAccount::where('type', 'Asset')->where(function($q) {
                $q->where('code', '1200')->orWhere('name', 'Inventory');
            })->first();
            $discountReceived = \App\Models\ChartOfAccount::where('type', 'Income')->where('name', 'Discount Received')->first();
            $vendorModel = \App\Models\Vendor::find($purchase->vendor_id);
            $vendorAccount = $vendorModel ? $vendorModel->account_id ?? ($vendorModel->account->id ?? null) : null;
            if (!$inventoryAccount || !$vendorAccount) {
                \Log::error('PurchaseController: Required account(s) not found for purchase journal entry.', [
                    'inventoryAccount' => $inventoryAccount,
                    'vendorAccount' => $vendorAccount
                ]);
                throw new \Exception('Required account(s) not found for purchase journal entry.');
            }
            // Generate journal entry number
            $last = \App\Models\JournalEntry::where('entry_number', 'like', 'PUR-%')->orderByDesc('id')->first();
            $nextNum = $last ? (intval(substr($last->entry_number, 4)) + 1) : 1;
            $entryNumber = 'PUR-' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);
            $entry = new \App\Models\JournalEntry([
                'entry_number' => $entryNumber,
                'entry_date' => $purchase->purchase_date,
                'date' => $purchase->purchase_date,
                'description' => 'Purchase Invoice #' . $purchase->purchase_number,
                'reference_type' => 'purchase',
                'reference_id' => $purchase->id,
                'created_by' => auth()->id() ?? 1,
            ]);
            $entry->save();
            // Debit Inventory
            $entry->lines()->create([
                'account_id' => $inventoryAccount->id,
                'debit' => $amount,
                'credit' => 0,
                'description' => 'Inventory Purchase'
            ]);
            // Credit Vendor
            $entry->lines()->create([
                'account_id' => $vendorAccount,
                'debit' => 0,
                'credit' => $net,
                'description' => 'Payable to Vendor'
            ]);
            // Debit Discount Received (if any)
            if ($discount > 0 && $discountReceived) {
                $entry->lines()->create([
                    'account_id' => $discountReceived->id,
                    'debit' => 0,
                    'credit' => $discount,
                    'description' => 'Discount Received'
                ]);
            }
        });
        // --- End Update Journal Entries ---

        return redirect()->route('purchases.edit', $purchase->id)->with('success', 'Purchase updated successfully!');
    }
    public function destroy($id) {
        $purchase = \App\Models\Purchase::findOrFail($id);
        // Delete all purchase items
        $purchase->items()->delete();
        // Find and delete related journal entries and their lines
        $journalEntries = \App\Models\JournalEntry::where('reference_type', 'purchase')->where('reference_id', $purchase->id)->get();
        foreach ($journalEntries as $entry) {
            $entry->lines()->delete();
            $entry->delete();
        }
        $purchase->delete();
        return redirect()->route('purchases.create')->with('success', 'Purchase deleted.');
    }
    public function create(Request $request) {
        $vendors = \App\Models\Vendor::orderBy('name')->get();
        $products = \App\Models\Inventory::orderBy('name')->get()->map(function($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'unit_name' => $p->unit,
                'buy_price' => $p->buy_price,
            ];
        })->values()->toArray();
        $accounts = \App\Models\ChartOfAccount::where('type', 'Asset')->orderBy('name')->get();
        // Recent purchases with search and pagination
        $search = $request->input('search');
        $purchasesQuery = \App\Models\Purchase::with(['vendor', 'items.product', 'items.unit'])->orderByDesc('purchase_date');
        if ($search) {
            $purchasesQuery->where(function($q) use ($search) {
                $q->where('purchase_number', 'like', "%$search%")
                  ->orWhereHas('vendor', function($qc) use ($search) {
                      $qc->where('name', 'like', "%$search%");
                  });
            });
        }
        $purchases = $purchasesQuery->paginate(10)->withQueryString();
        return view('purchases.create', compact('vendors', 'products', 'accounts', 'purchases', 'search'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'purchase_date' => 'required|date',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:inventories,id',
            'products.*.quantity' => 'required|numeric|min:0.01',
            'products.*.unit_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Generate unique purchase_number
        $lastPurchase = \App\Models\Purchase::orderByDesc('id')->first();
        $nextNumber = $lastPurchase ? ((int)substr($lastPurchase->purchase_number, 4)) + 1 : 1;
        $purchaseNumber = 'PUR-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        $purchase = new \App\Models\Purchase();
        $purchase->purchase_number = $purchaseNumber;
        $purchase->vendor_id = $validated['vendor_id'];
        $purchase->purchase_date = $validated['purchase_date'];
        $purchase->discount_amount = $validated['discount'] ?? 0;
        $purchase->notes = $validated['notes'] ?? null;
        $purchase->payment_status = 'Unpaid'; // Set default payment status
        $purchase->total_amount = 0; // will update after items
        $purchase->net_amount = 0; // will update after items
        $purchase->save();

        $subtotal = 0;
        foreach ($validated['products'] as $item) {
    $product = \App\Models\Inventory::find($item['product_id']);
    $lineTotal = $item['quantity'] * $item['unit_price'];
    if (!$product) {
        throw new \Exception('Selected inventory product not found.');
    }
    // Find or create the product category in products table
    $inventoryCategory = $product->category;
    $productCategoryId = null;
    if ($inventoryCategory) {
        $productCategory = \App\Models\ProductCategory::firstOrCreate(
            ['name' => $inventoryCategory->name],
            ['description' => $inventoryCategory->name]
        );
        $productCategoryId = $productCategory->id;
    }
    // Find or create the unit
    $unit = \App\Models\Unit::where('abbreviation', $product->unit)
        ->orWhere('name', $product->unit)
        ->first();
    if (!$unit) {
        throw new \Exception('Unit not found for inventory product.');
    }
    // Find or create the product in the products table
    $productModel = \App\Models\Product::firstOrCreate(
        [
            'name' => $product->name,
            'unit_id' => $unit->id
        ],
        [
            'category_id' => $productCategoryId,
            'sale_price' => $product->sale_price,
            'buy_price' => $product->buy_price,
            'created_at' => now(),
            'updated_at' => now()
        ]
    );
    $purchaseItem = $purchase->items()->create([
        'product_id' => $item['product_id'],
        'quantity' => $item['quantity'],
        'rate' => $item['unit_price'],
        'unit_id' => $unit->id,
        'amount' => $lineTotal,
    ]);
    $subtotal += $lineTotal;
}

        $purchase->total_amount = $subtotal;
        $purchase->net_amount = $subtotal - ($validated['discount'] ?? 0);
        $purchase->save();

        // --- Create Journal Entry for Purchase ---
        \DB::transaction(function () use ($purchase) {
            $amount = $purchase->total_amount;
            $discount = $purchase->discount_amount ?? 0;
            $net = $purchase->net_amount;
            // Get relevant accounts
            $inventoryAccount = \App\Models\ChartOfAccount::where('type', 'Asset')->where(function($q) {
                $q->where('code', '1200')->orWhere('name', 'Inventory');
            })->first();
            $discountReceived = \App\Models\ChartOfAccount::where('type', 'Income')->where('name', 'Discount Received')->first();
            $vendorModel = \App\Models\Vendor::find($purchase->vendor_id);
            $vendorAccount = $vendorModel ? $vendorModel->account_id ?? ($vendorModel->account->id ?? null) : null;
            if (!$inventoryAccount || !$vendorAccount) {
                \Log::error('PurchaseController: Required account(s) not found for purchase journal entry.', [
                    'inventoryAccount' => $inventoryAccount,
                    'vendorAccount' => $vendorAccount
                ]);
                throw new \Exception('Required account(s) not found for purchase journal entry.');
            }
            // Generate journal entry number
            $last = \App\Models\JournalEntry::where('entry_number', 'like', 'PUR-%')->orderByDesc('id')->first();
            $nextNum = $last ? (intval(substr($last->entry_number, 4)) + 1) : 1;
            $entryNumber = 'PUR-' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);
            $entry = new \App\Models\JournalEntry([
                'entry_number' => $entryNumber,
                'entry_date' => $purchase->purchase_date,
                'date' => $purchase->purchase_date,
                'description' => 'Purchase Invoice #' . $purchase->purchase_number,
                'reference_type' => 'purchase',
                'reference_id' => $purchase->id,
                'created_by' => auth()->id() ?? 1,
            ]);
            $entry->save();
            // Debit Inventory
            $entry->lines()->create([
                'account_id' => $inventoryAccount->id,
                'debit' => $amount,
                'credit' => 0,
                'description' => 'Inventory Purchase'
            ]);
            // Credit Vendor
            $entry->lines()->create([
                'account_id' => $vendorAccount,
                'debit' => 0,
                'credit' => $net,
                'description' => 'Payable to Vendor'
            ]);
            // Debit Discount Received (if any)
            if ($discount > 0 && $discountReceived) {
                $entry->lines()->create([
                    'account_id' => $discountReceived->id,
                    'debit' => 0,
                    'credit' => $discount,
                    'description' => 'Discount Received'
                ]);
            }
        });
        // --- End Journal Entry ---

        if ($request->input('from_admin_copy')) {
            return redirect()->route('admin.purchase-form-copy')->with('success', 'Purchase created successfully!');
        }
        return redirect()->route('purchases.create')->with('success', 'Purchase created successfully!');
    }

    public function show($id) {
        $purchase = \App\Models\Purchase::with(['vendor', 'items.product', 'items.unit'])->findOrFail($id);
        return view('purchases.show', compact('purchase'));
    }
}
