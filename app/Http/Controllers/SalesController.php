<?php
namespace App\Http\Controllers;

use PDF;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Customer;
use Illuminate\Support\Str;

class SalesController extends Controller {
    public function index(Request $request) {
        $query = \App\Models\Sale::with('customer');

        // Set default date filters if not provided
        $from_date = $request->input('from_date', '2025-01-01');
        $to_date = $request->input('to_date', date('Y-m-d'));

        // Date range filter
        $query->whereDate('sale_date', '>=', $from_date);
        $query->whereDate('sale_date', '<=', $to_date);

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

        // Pass the default dates to the view
        return view('modules.sales', compact('sales', 'customers', 'from_date', 'to_date'));
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

    public function exportAllPdf(Request $request)
    {
        try {
            $query = Sale::with(['customer', 'items.product', 'items.unit']);

            // Apply filters
            if ($request->filled('from_date')) {
                $query->whereDate('sale_date', '>=', $request->from_date);
            }
            if ($request->filled('to_date')) {
                $query->whereDate('sale_date', '<=', $request->to_date);
            }
            if ($request->filled('customer_id')) {
                $query->where('customer_id', $request->customer_id);
            }
            if ($request->filled('status')) {
                $query->where('payment_status', $request->status);
            }

            $sales = $query->orderBy('sale_date')->get();
            
            $pdf = PDF::loadView('pdfs.sales-table', [
                'sales' => $sales,
                'from' => $request->from_date,
                'to' => $request->to_date
            ]);
            
            return $pdf->download('sales-report-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \Log::error('PDF Export Error: ' . $e->getMessage());
            return back()->with('error', 'Error generating PDF: ' . $e->getMessage());
        }
    }

    public function destroy($id) {
        try {
            \DB::beginTransaction();
            
            $sale = Sale::findOrFail($id);
            
            // Delete related journal entries
            $journalEntries = \App\Models\JournalEntry::where('reference_type', 'sale')
                ->where('reference_id', $sale->id)
                ->get();

            foreach ($journalEntries as $entry) {
                $entry->lines()->delete();  // Delete journal entry lines
                $entry->delete();  // Delete journal entry
            }

            // Delete sale items
            $sale->items()->delete();
            
            // Delete the sale
            $sale->delete();

            \DB::commit();
            return back()->with('success', 'Sale deleted successfully!');
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Sale deletion error: ' . $e->getMessage());
            return back()->with('error', 'Error deleting sale: ' . $e->getMessage());
        }
    }
}
