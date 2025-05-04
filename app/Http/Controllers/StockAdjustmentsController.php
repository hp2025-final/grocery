<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class StockAdjustmentsController extends Controller {
    public function index(Request $request) {
        $query = \App\Models\StockAdjustment::with('user');

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('adjustment_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('adjustment_date', '<=', $request->to_date);
        }
        // User filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        // Search (adjustment no or notes)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('adjustment_number', 'like', "%$search%")
                  ->orWhere('notes', 'like', "%$search%");
            });
        }
        $adjustments = $query->orderByDesc('adjustment_date')->paginate(15)->withQueryString();
        $users = \App\Models\User::orderBy('name')->get();
        return view('stock_adjustments.index', compact('adjustments', 'users'));
    }
}
