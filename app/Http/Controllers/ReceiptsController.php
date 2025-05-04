<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class ReceiptsController extends Controller {
    public function index(Request $request) {
        $query = \App\Models\CustomerReceipt::with('customer');

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('receipt_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('receipt_date', '<=', $request->to_date);
        }
        // Customer filter
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        // Search (receipt no or customer name)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('receipt_number', 'like', "%$search%")
                  ->orWhereHas('customer', function($qc) use ($search) {
                      $qc->where('name', 'like', "%$search%");
                  });
            });
        }
        $receipts = $query->orderByDesc('receipt_date')->paginate(15)->withQueryString();
        $customers = \App\Models\Customer::orderBy('name')->get();
        return view('modules.receipts', compact('receipts', 'customers'));
    }
}
