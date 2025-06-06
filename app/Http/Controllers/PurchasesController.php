<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PurchasesController extends Controller {
    public function index(Request $request) {
        $query = \App\Models\Purchase::with('vendor');

        // Get date filters with defaults
        $from_date = $request->input('from_date', '2025-01-01');
        $to_date = $request->input('to_date', '2025-06-04');

        // Apply date filters
        $query->whereDate('purchase_date', '>=', $from_date);
        $query->whereDate('purchase_date', '<=', $to_date);

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

        // Pass default dates to view
        return view('modules.purchases', compact('purchases', 'vendors', 'from_date', 'to_date'));
    }

    public function exportPdf($id)
    {
        $purchase = \App\Models\Purchase::with(['vendor', 'items.product', 'items.unit'])->findOrFail($id);
        
        $pdf = \PDF::loadView('pdfs.purchase-invoice', compact('purchase'));
        
        $filename = sprintf(
            'invoice-%s-%s-%s.pdf',
            Str::slug($purchase->vendor->name),
            $purchase->purchase_number,
            date('Y-m-d', strtotime($purchase->purchase_date))
        );
        
        return $pdf->download($filename);
    }
}
