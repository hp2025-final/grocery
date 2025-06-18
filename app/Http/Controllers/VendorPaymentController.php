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

    public function createFromPurchase($purchaseId)
    {
        $purchase = \App\Models\Purchase::with('vendor')->findOrFail($purchaseId);
        
        // Check if purchase exists and is unpaid
        if (!$purchase) {
            return redirect()->route('purchases.index')->with('error', 'Purchase not found.');
        }
        
        if ($purchase->payment_status === 'Paid') {
            return redirect()->route('purchases.index')->with('error', 'This purchase is already paid.');
        }

        // Get all active banks
        $banks = \App\Models\Bank::orderBy('name')->get();
        
        return view('purchases._create_payment_modal', compact('purchase', 'banks'));
    }

    public function storeFromPurchase(Request $request)
    {
        try {
            \Log::info('Received request data:', $request->all());

            $validated = $request->validate([
                'purchase_id' => 'required|exists:purchases,id',
                'payment_account_id' => 'required|exists:banks,id',
                'notes' => 'nullable|string|max:1000',
            ]);

            DB::transaction(function () use ($request) {
                $purchase = \App\Models\Purchase::with('vendor')->findOrFail($request->purchase_id);
                
                if ($purchase->payment_status === 'Paid') {
                    throw new \Exception('This purchase is already paid.');
                }

                // Get bank account ID from Bank model
                $bank = \App\Models\Bank::findOrFail($request->payment_account_id);
                if (!$bank || !$bank->account_id) {
                    throw new \Exception('Selected bank does not have a linked account.');
                }

                // Auto-generate payment number
                $last = VendorPayment::orderByDesc('id')->first();
                $nextNum = $last ? (intval(substr($last->payment_number, 4)) + 1) : 1;
                $payment_number = 'PAY-' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);

                // Create payment
                $payment = VendorPayment::create([
                    'payment_number' => $payment_number,
                    'vendor_id' => $purchase->vendor_id,
                    'payment_date' => now(),
                    'amount_paid' => $purchase->net_amount,
                    'payment_account_id' => $bank->account_id,
                    'bank_id' => $bank->id,
                    'payment_method' => 'Bank',
                    'reference' => 'Purchase #' . $purchase->purchase_number,
                    'notes' => $request->notes,
                ]);

                // Create journal entry
                $journal = new \App\Models\JournalEntry();
                $lastEntry = \App\Models\JournalEntry::orderByRaw('CAST(entry_number AS UNSIGNED) DESC')->first();
                $nextEntryNumber = $lastEntry ? ((int)$lastEntry->entry_number + 1) : 1;
                
                $journal->entry_number = (string)$nextEntryNumber;
                $journal->date = now();
                $journal->description = 'Payment to vendor: ' . $purchase->vendor->name . ' for Purchase #' . $purchase->purchase_number;
                $journal->reference_type = 'vendor_payment';
                $journal->reference_id = $payment->id;
                $journal->created_by = auth()->id();
                $journal->save();

                // Create journal entry lines
                $journal->lines()->createMany([
                    [
                        'account_id' => $purchase->vendor->account_id,
                        'debit' => $purchase->net_amount,
                        'credit' => null,
                        'description' => 'Vendor Payment for Purchase #' . $purchase->purchase_number,
                    ],
                    [
                        'account_id' => $bank->account_id,
                        'debit' => null,
                        'credit' => $purchase->net_amount,
                        'description' => 'Payment from Bank',
                    ],
                ]);

                // Update purchase status to Paid
                $purchase->payment_status = 'Paid';
                $purchase->save();
            });

            return redirect()->route('purchases.index')->with('success', 'Payment created successfully.');
        } catch (\Exception $e) {
            \Log::error('Error in storeFromPurchase:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Error creating payment: ' . $e->getMessage()]);
        }
    }
}

