<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\VendorPayment;
use App\Models\Bank;
use App\Models\Vendor;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ChartOfAccount;
use PDF;

class VendorPaymentController extends Controller {
    /**
     * AJAX search/paginate vendor payments by vendor name.
     * Params: search (vendor), page
     */
    public function liveSearch(Request $request)
    {
        $search = $request->input('search');
        $page = $request->input('page', 1);
        $query = \App\Models\VendorPayment::with(['vendor', 'paymentAccount']);
        if ($search) {
            $query->whereHas('vendor', function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%');
            });
        }
        $payments = $query->orderByDesc('payment_date')->paginate(10, ['*'], 'page', $page);
        return response()->json([
            'html' => view('vendor_payments._payments_table', compact('payments'))->render()
        ]);
    }
    
    public function create() {
        $vendors = Vendor::orderBy('name')->get();
        $banks = Bank::orderBy('name')->get();
        $payments = VendorPayment::with(['vendor', 'paymentAccount'])
            ->orderByDesc('payment_date')
            ->paginate(10);
        return view('vendor_payments.create', compact('vendors', 'banks', 'payments'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'entries' => 'required|array|min:1',
            'entries.*.vendor_id' => 'required|exists:vendors,id',
            'entries.*.payment_date' => 'required|date',
            'entries.*.amount_paid' => 'required|numeric|min:0.01',
            'entries.*.payment_account_id' => 'required|exists:banks,id',
            'entries.*.notes' => 'nullable|string|max:1000',
            'entries.*.payment_method' => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['entries'] as $entry) {
                // Get Bank and its account
                $bank = Bank::find($entry['payment_account_id']);
                if (!$bank || !$bank->account_id) {
                    throw new \Exception('Selected bank does not have a linked account.');
                }

                $payment = new VendorPayment();
                
                // Set the ChartOfAccount id from the bank's linked account
                $payment->payment_account_id = $bank->account_id;
                
                $payment->vendor_id = $entry['vendor_id'];
                $payment->payment_date = $entry['payment_date'];
                $payment->amount_paid = $entry['amount_paid'];
                $payment->notes = $entry['notes'] ?? null;
                $payment->payment_method = 'bank';

                // Generate payment number (PV-00001 format)
                $lastPayment = VendorPayment::orderByDesc('id')->first();
                if ($lastPayment && preg_match('/PV-(\d+)/', $lastPayment->payment_number, $matches)) {
                    $nextNumber = intval($matches[1]) + 1;
                } else {
                    $nextNumber = 1;
                }
                $payment->payment_number = 'PV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
                $payment->save();

                // Create Journal Entry for payment
                $vendor = Vendor::findOrFail($entry['vendor_id']); 
                $amount = (float) $entry['amount_paid'];
                
                $journal = new JournalEntry();
                $lastEntry = JournalEntry::orderByRaw('CAST(entry_number AS UNSIGNED) DESC')->first();
                $nextEntryNumber = $lastEntry ? ((int)$lastEntry->entry_number + 1) : 1;
                
                $journal->entry_number = (string)$nextEntryNumber;
                $journal->date = $entry['payment_date'];
                $journal->description = 'Payment to vendor: ' . $vendor->name;
                $journal->reference_type = 'vendor_payment';
                $journal->reference_id = $payment->id;
                $journal->created_by = auth()->id();
                $journal->created_at = now();
                $journal->updated_at = now();
                $journal->save();

                // Debit: Vendor, Credit: Bank
                $journal->lines()->createMany([
                    [
                        'account_id' => $vendor->account_id, // Vendor account (Debit)
                        'debit' => $amount,
                        'credit' => null,
                        'description' => 'Vendor payment',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'account_id' => $payment->payment_account_id, // Bank account (Credit)
                        'debit' => null,
                        'credit' => $amount,
                        'description' => 'Bank/Cash payment',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            }
        });

        return back()->with('success', 'Vendor payments recorded successfully!');
    }

    public function destroy($id) {
        $payment = VendorPayment::findOrFail($id);
        // Find related journal entry
        $journal = JournalEntry::where('reference_type', 'vendor_payment')
            ->where('reference_id', $payment->id)
            ->first();
        if ($journal) {
            // Delete all journal entry lines
            $journal->lines()->delete();
            // Delete the journal entry
            $journal->delete();
        }
        // Delete the vendor payment
        $payment->delete();
        return back()->with('success', 'Vendor payment and related journal entries deleted successfully!');
    }

    public function exportPdf($id)
    {
        $payment = VendorPayment::with(['vendor', 'paymentAccount', 'user'])
            ->findOrFail($id);

        $pdf = PDF::loadView('vendor_payments.payment_pdf', [
            'payment' => $payment
        ]);

        return $pdf->download('payment_'.$payment->payment_number.'.pdf');
    }

    public function exportTable(Request $request)
    {
        $query = VendorPayment::with(['vendor', 'paymentAccount']);
        
        // Apply search filter if exists
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('vendor', function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%');
            });
        }

        $payments = $query->orderByDesc('payment_date')->get();

        $pdf = PDF::loadView('vendor_payments.table_pdf', [
            'payments' => $payments
        ]);

        return $pdf->download('vendor_payments_'.date('Y-m-d').'.pdf');
    }
}

