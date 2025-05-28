<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class SalesController extends Controller {
    public function index(Request $request) {
        $query = \App\Models\Sale::with('customer');

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('sale_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('sale_date', '<=', $request->to_date);
        }
        // Customer filter
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        // Status filter
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }
        // Search (invoice no or customer name)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('sale_number', 'like', "%$search%")
                  ->orWhereHas('customer', function($qc) use ($search) {
                      $qc->where('name', 'like', "%$search%");
                  });
            });
        }
        $sales = $query->orderByDesc('sale_date')->paginate(15)->withQueryString();
        $customers = \App\Models\Customer::orderBy('name')->get();
        return view('modules.sales', compact('sales', 'customers'));
    }

    public function exportPdf($id)
    {
        $sale = \App\Models\Sale::with(['customer', 'items.product', 'items.unit'])->findOrFail($id);
        
        $pdf = \PDF::loadView('pdfs.sale-invoice-new', compact('sale'));
        
        $filename = sprintf(
            'invoice-%s-%s-%s.pdf',
            \Str::slug($sale->customer->name),
            $sale->sale_number,
            date('Y-m-d', strtotime($sale->sale_date))
        );
        
        return $pdf->download($filename);
    }
}
