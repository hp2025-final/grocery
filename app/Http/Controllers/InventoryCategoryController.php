<?php
namespace App\Http\Controllers;
use App\Models\InventoryCategory;
use Illuminate\Http\Request;

class InventoryCategoryController extends Controller
{
    public function edit($id)
    {
        $category = InventoryCategory::findOrFail($id);
        $last = InventoryCategory::orderBy('id', 'desc')->first();
        $nextNumber = $last ? intval(substr($last->code, 4)) + 1 : 1;
        $nextCode = 'INC-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        $allCategories = InventoryCategory::orderBy('created_at', 'desc')->get();
        return view('inventory_categories.create', compact('category', 'nextCode', 'allCategories'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $category = InventoryCategory::findOrFail($id);
        $category->update($validated);
        return redirect()->route('inventory-categories.create')->with('success', 'Category updated successfully!');
    }

    public function destroy($id)
    {
        $category = InventoryCategory::findOrFail($id);
        $category->delete();
        return back()->with('success', 'Category deleted successfully!');
    }
    public function index()
    {
        $categories = \App\Models\InventoryCategory::orderByDesc('created_at')->paginate(15);
        return view('inventory_categories.index', compact('categories'));
    }
    public function create()
    {
        // Pre-generate next code for display
        $last = InventoryCategory::orderBy('id', 'desc')->first();
        $nextNumber = $last ? intval(substr($last->code, 4)) + 1 : 1;
        $nextCode = 'INC-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        $allCategories = InventoryCategory::orderBy('created_at', 'desc')->get();
        return view('inventory_categories.create', compact('nextCode', 'allCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $category = new InventoryCategory($validated);
        $category->created_at = '2025-01-01 00:00:00';
        $category->updated_at = '2025-01-01 00:00:00';
        $category->save();
        return back()->with('success', 'Inventory category created successfully!');
    }
}
