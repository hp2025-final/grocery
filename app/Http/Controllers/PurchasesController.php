<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class PurchasesController extends Controller {
    public function index(Request $request) {
        $query = \App\Models\Purchase::with('vendor');

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('purchase_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('purchase_date', '<=', $request->to_date);
        }
        // Vendor filter
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        // Status filter
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }
        // Search (purchase no or vendor name)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('purchase_number', 'like', "%$search%")
                  ->orWhereHas('vendor', function($qc) use ($search) {
                      $qc->where('name', 'like', "%$search%");
                  });
            });
        }
        $purchases = $query->orderByDesc('purchase_date')->paginate(15)->withQueryString();
        $vendors = \App\Models\Vendor::orderBy('name')->get();
        return view('modules.purchases', compact('purchases', 'vendors'));
    }

    public function exportPdf($id)
    {
        $purchase = \App\Models\Purchase::with(['vendor', 'items.product', 'items.unit'])->findOrFail($id);
        
        $pdf = \PDF::loadView('pdfs.purchase-invoice', compact('purchase'));
        
        return $pdf->download('invoice-' . $purchase->purchase_number . '.pdf');
    }
}
