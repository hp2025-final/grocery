<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Bank;
use App\Models\CustomerReceipt;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB; // for transaction
use PDF;

class CustomerReceiptController extends Controller
{
    /**
     * AJAX search/sort/paginate receipts by customer name.
     * Params: search (customer), sort (column), direction (asc/desc), page
     */
    public function liveSearch(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'receipt_date'); // Changed default sort to receipt_date
        $direction = $request->input('direction', 'desc'); // Default to descending
        $page = $request->input('page', 1);

        $query = \App\Models\CustomerReceipt::with(['customer', 'bank', 'paymentAccount']);

        if ($search) {
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%');
            });
        }

        // Only allow sorting on specific columns
        $sortable = ['receipt_date', 'amount_received'];
        if (in_array($sort, $sortable)) {
            $query->orderBy($sort, $direction);
        } elseif ($sort === 'customer') {
            $query->join('customers', 'customer_receipts.customer_id', '=', 'customers.id')
                  ->orderBy('customers.name', $direction)
                  ->select('customer_receipts.*');
        } elseif ($sort === 'bank') {
            $query->join('banks', 'customer_receipts.bank_id', '=', 'banks.id')
                  ->orderBy('banks.name', $direction)
                  ->select('customer_receipts.*');
        } else {
            $query->orderBy('receipt_date', 'desc');
        }
        $receipts = $query->paginate(10, ['*'], 'page', $page);
        return response()->json([
            'html' => view('customer_receipts._receipts_table', compact('receipts'))->render()
        ]);
    }
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $banks = Bank::orderBy('name')->get();
        $receipts = \App\Models\CustomerReceipt::with(['customer', 'bank'])
            ->orderBy('receipt_date', 'desc')
            ->paginate(10);
        return view('customer_receipts.create', compact('customers', 'banks', 'receipts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entries' => 'required|array|min:1',
            'entries.*.customer_id' => 'required|exists:customers,id',
            'entries.*.receipt_date' => 'required|date',
            'entries.*.amount_received' => 'required|numeric|min:0.01',
            'entries.*.payment_account_id' => 'required|integer|exists:banks,id',
            'entries.*.payment_method' => 'nullable|string|max:50',
            'entries.*.reference' => 'nullable|string|max:100',
            'entries.*.notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['entries'] as $entry) {
                // Save the original bank id
                $bank_id_from_form = $entry['payment_account_id'];
                $bank = Bank::find($bank_id_from_form);

                if (!$bank || !$bank->account_id) {
                    throw new \Exception('Selected bank does not have a linked account.');
                }

                // Set the ChartOfAccount id for payment_account_id and the Bank id for bank_id
                $entry['payment_account_id'] = $bank->account_id;
                $entry['bank_id'] = $bank->id;

                // Auto-generate receipt_number in RPT-XXXXXX format
                $last = CustomerReceipt::orderByDesc('id')->first();
                $nextNum = $last ? (intval(substr($last->receipt_number, 4)) + 1) : 1;
                $receipt_number = 'RPT-' . str_pad($nextNum, 6, '0', STR_PAD_LEFT);

                $receipt = CustomerReceipt::create(array_merge($entry, [
                    'receipt_number' => $receipt_number,
                ]));

                // Create Journal Entry for receipt
                $customer = Customer::findOrFail($entry['customer_id']);
                $amount = (float) $entry['amount_received'];
                
                $journal = new JournalEntry();
                $lastEntry = JournalEntry::orderByRaw('CAST(entry_number AS UNSIGNED) DESC')->first();
                $nextEntryNumber = $lastEntry ? ((int)$lastEntry->entry_number + 1) : 1;
                
                $journal->entry_number = (string)$nextEntryNumber;
                $journal->date = $entry['receipt_date'];
                $journal->description = 'Receipt from customer: ' . $customer->name;
                $journal->reference_type = 'customer_receipt';
                $journal->reference_id = $receipt->id;
                $journal->created_by = auth()->id();
                $journal->created_at = now();
                $journal->updated_at = now();
                $journal->save();

                // Debit: Bank, Credit: Customer
                $journal->lines()->createMany([
                    [
                        'account_id' => $entry['payment_account_id'], // Bank account
                        'debit' => $amount,
                        'credit' => null,
                        'description' => 'Amount received from customer',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'account_id' => $customer->account_id, // Customer account
                        'debit' => null,
                        'credit' => $amount,
                        'description' => 'Customer payment',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            }
        });

        return redirect()->route('customer-receipts.create')
            ->with('success', count($validated['entries']) . ' customer receipt(s) saved successfully.');
    }

    public function destroy(
        $id
    ) {
        DB::transaction(function () use ($id) {
            $receipt = CustomerReceipt::findOrFail($id);
            // Find related journal entries (by reference_type and reference_id if available, else by custom logic)
            $journalEntries = JournalEntry::where('reference_type', 'customer_receipt')
                ->where('reference_id', $receipt->id)
                ->get();
            foreach ($journalEntries as $entry) {
                $entry->lines()->delete();
                $entry->delete();
            }
            $receipt->delete();
        });
        return back()->with('success', 'Customer receipt and related journal entries deleted successfully.');
    }

    public function exportPdf($id)
    {
        $receipt = CustomerReceipt::with(['customer', 'paymentAccount', 'user'])
            ->findOrFail($id);

        $pdf = PDF::loadView('customer_receipts.receipt_pdf', [
            'receipt' => $receipt
        ]);

        return $pdf->download('receipt_'.$receipt->receipt_number.'.pdf');
    }

    public function exportTable(Request $request)
    {
        $query = CustomerReceipt::with(['customer', 'paymentAccount']);
        
        // Apply search filter if exists
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%');
            });
        }

        $receipts = $query->orderByDesc('receipt_date')->get();

        $pdf = PDF::loadView('customer_receipts.table_pdf', [
            'receipts' => $receipts
        ]);

        return $pdf->download('customer_receipts_'.date('Y-m-d').'.pdf');
    }
}

