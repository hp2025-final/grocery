<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class StockAdjustmentController extends Controller {
    public function create() {
        $products = \App\Models\Product::with('unit')->orderBy('name')->get();
        return view('stock_adjustments.create', compact('products'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'adjustment_type' => 'required|in:Increase,Decrease',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:0.01',
            'products.*.unit_price' => 'required|numeric|min:0',
        ]);

        $adjustment = new \App\Models\StockAdjustment();
        $adjustment->adjustment_type = $validated['adjustment_type'];
        $adjustment->date = $validated['date'];
        $adjustment->notes = $validated['notes'] ?? null;
        $adjustment->total_amount = 0; // will update after items
        $adjustment->save();

        $subtotal = 0;
        foreach ($validated['products'] as $item) {
            $product = \App\Models\Product::find($item['product_id']);
            $lineTotal = $item['quantity'] * $item['unit_price'];
            $adjustment->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_id' => $product->unit_id,
                'unit_price' => $item['unit_price'],
                'total' => $lineTotal,
            ]);
            $subtotal += $lineTotal;
        }
        $adjustment->total_amount = $subtotal;
        $adjustment->save();
        // Observer will handle journal entry and inventory update
        return redirect()->route('stock-adjustments.index')->with('success', 'Stock adjustment recorded successfully!');
    }
}
