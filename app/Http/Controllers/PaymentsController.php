<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class PaymentsController extends Controller {
    public function index(Request $request) {
        $query = \App\Models\VendorPayment::with('vendor');

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }
        // Vendor filter
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        // Search (payment no or vendor name)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('payment_number', 'like', "%$search%")
                  ->orWhereHas('vendor', function($qc) use ($search) {
                      $qc->where('name', 'like', "%$search%");
                  });
            });
        }
        $payments = $query->orderByDesc('payment_date')->paginate(15)->withQueryString();
        $vendors = \App\Models\Vendor::orderBy('name')->get();
        return view('modules.payments', compact('payments', 'vendors'));
    }
}
