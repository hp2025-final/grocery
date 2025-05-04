<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
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
        $vendors = \App\Models\Vendor::orderBy('name')->get();
        // Get only ChartOfAccount records that are linked to a bank
        $bankAccounts = \App\Models\Bank::with('account')->get()->pluck('account')->filter();
        $payments = \App\Models\VendorPayment::with(['vendor', 'paymentAccount'])
            ->orderByDesc('payment_date')
            ->paginate(10);
        return view('vendor_payments.create', compact('vendors', 'bankAccounts', 'payments'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'payment_date' => 'required|date',
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_account_id' => 'required|exists:chart_of_accounts,id',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $payment = new \App\Models\VendorPayment();
        $payment->vendor_id = $validated['vendor_id'];
        $payment->payment_date = $validated['payment_date'];
        $payment->amount_paid = $validated['amount_paid'];
        $payment->payment_account_id = $validated['payment_account_id'];
        $payment->reference = $validated['reference'] ?? null;
        $payment->notes = $validated['notes'] ?? null;
        $payment->payment_method = 'bank'; // Default to bank for now
        // Generate unique payment_number like PV-00001
        $lastPayment = \App\Models\VendorPayment::orderByDesc('id')->first();
        if ($lastPayment && preg_match('/PV-(\d+)/', $lastPayment->payment_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }
        $payment->payment_number = 'PV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        $payment->save();
        // Always create a journal entry, even if amount_paid is zero
        $amount = isset($validated['amount_paid']) ? (float) $validated['amount_paid'] : 0.0;
        $vendor = \App\Models\Vendor::find($validated['vendor_id']);
        $vendorAccount = $vendor ? \App\Models\ChartOfAccount::find($vendor->account_id) : null;
        $bankAccount = \App\Models\ChartOfAccount::find($validated['payment_account_id']);
        if (!$vendorAccount || !$bankAccount) {
            return back()->withErrors(['error' => 'Required accounts not found in chart of accounts.'])->withInput();
        }
        $journal = new \App\Models\JournalEntry();
        $journal->date = $validated['payment_date'];
        $journal->description = 'Vendor payment for vendor: ' . $payment->vendor_id;
        $journal->reference_type = 'vendor_payment';
        $journal->reference_id = $payment->id;
        // Generate unique entry_number like PV-00001
        $lastEntry = \App\Models\JournalEntry::where('entry_number', 'like', 'PV-%')->orderByDesc('id')->first();
        if ($lastEntry && preg_match('/PV-(\d+)/', $lastEntry->entry_number, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }
        $journal->entry_number = 'PV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        $journal->created_by = auth()->id();
        $journal->save();
        $journal->lines()->createMany([
            [
                'account_id' => $vendorAccount->id,
                'debit' => $amount,
                'credit' => null,
                'description' => 'Vendor payment',
            ],
            [
                'account_id' => $bankAccount->id,
                'debit' => null,
                'credit' => $amount,
                'description' => 'Bank/Cash',
            ],
        ]);
        return back()->with('success', 'Vendor payment recorded successfully!');
    }

    public function destroy($id) {
        $payment = \App\Models\VendorPayment::findOrFail($id);
        // Find related journal entry
        $journal = \App\Models\JournalEntry::where('reference_type', 'vendor_payment')
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
}

