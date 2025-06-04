<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bank;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\Customer;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\BankTransfer;
use App\Models\CustomerReceipt;
use App\Models\VendorPayment;

class BankLedgerController extends Controller
{
    public function show($bankId, Request $request)
    {
        $bank = Bank::findOrFail($bankId);
        
        // Get date filters with defaults
        $from = $request->input('from', '2025-01-01');
        $to = $request->input('to', date('Y-m-d'));

        $accountId = $bank->account_id;

        // Get opening balance if date filter is applied
        $openingBalance = null;
        $balance = 0;
        if ($from) {
            $openingBalance = $this->calculateOpeningBalance($accountId, $from);
            $balance = $openingBalance;
        }

        // First get all entries up to the current page to calculate correct running balance
        $currentPage = $request->input('page', 1);
        $perPage = 20;
        
        $query = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entry_lines.account_id', $accountId);

        if ($from) {
            $query->where(function($q) use ($from) {
                $q->where('journal_entries.date', '>=', $from)
                  ->orWhere(function($q2) {
                      $q2->where('journal_entries.reference_type', 'bank')
                         ->where('journal_entries.description', 'Bank opening balance');
                  });
            });
        }
        if ($to) $query->where('journal_entries.date', '<=', $to);

        // Apply search filter if present
        $search = $request->input('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('journal_entries.description', 'like', "%$search%")
                  ->orWhere('journal_entries.entry_number', 'like', "%$search%")
                  ->orWhere('journal_entries.reference_type', 'like', "%$search%")
                  ->orWhere('journal_entry_lines.debit', 'like', "%$search%")
                  ->orWhere('journal_entry_lines.credit', 'like', "%$search%");
            });
        }

        // Clone query for all previous entries
        $previousEntriesQuery = clone $query;
        $allPreviousEntries = $previousEntriesQuery
            ->orderBy('journal_entries.date')
            ->orderBy('journal_entries.id')
            ->limit(($currentPage - 1) * $perPage)
            ->get();

        // Calculate balance including all previous entries
        foreach ($allPreviousEntries as $entry) {
            $balance += ($entry->debit ?? 0) - ($entry->credit ?? 0);
        }

        // Get paginated entries
        $entries = $query->orderBy('journal_entries.date')
            ->orderBy('journal_entries.id')
            ->select(
                'journal_entries.date',
                'journal_entries.description',
                'journal_entries.entry_number',
                'journal_entries.reference_type',
                'journal_entries.reference_id',
                'journal_entry_lines.debit',
                'journal_entry_lines.credit',
                'journal_entries.created_at'
            )
            ->paginate($perPage)
            ->withQueryString();

        $rows = [];
        
        // Process entries
        foreach ($entries as $row) {
            $debit = $row->debit ?? 0;
            $credit = $row->credit ?? 0;
            $balance += ($debit - $credit);
            
            // Show related customer/vendor account name in description
            $description = '';
            $notes = '';
            $reference = '';
            $customer_id = null;
            $vendor_id = null;
            $type = $row->reference_type;

            // For bank opening balance entries
            if ($type === 'bank' && $row->description === 'Bank opening balance') {
                $type = 'Opening balance for bank: ' . $bank->name;
                $description = '';
            }
            // Process different transaction types
            elseif ($row->reference_type === 'customer_receipt') {
                $receipt = CustomerReceipt::find($row->reference_id);
                if ($receipt) {
                    if ($receipt->customer_id) {
                        $customer = Customer::find($receipt->customer_id);
                        if ($customer) {
                            $description = $customer->name;
                        }
                    }
                    $notes = $receipt->notes ?? '';
                    $reference = $receipt->reference ?? '';
                    $customer_id = $receipt->customer_id;
                }
            } elseif ($row->reference_type === 'vendor_payment') {
                $payment = VendorPayment::find($row->reference_id);
                if ($payment) {
                    if ($payment->vendor_id) {
                        $vendor = Vendor::find($payment->vendor_id);
                        if ($vendor) {
                            $description = $vendor->name;
                        }
                    }
                    $notes = $payment->notes ?? '';
                    $reference = $payment->reference ?? '';
                    $vendor_id = $payment->vendor_id;
                }
            } elseif ($row->reference_type === 'bank_transfer') {
                $transfer = BankTransfer::with(['fromBank', 'toBank'])->find($row->reference_id);
                if ($transfer) {
                    $description = 'Transfer: ' . $transfer->fromBank->name . ' → ' . $transfer->toBank->name;
                    $notes = $transfer->description ?? '';
                }
            } elseif ($row->reference_type === 'expense') {
                $expense = \App\Models\Expense::find($row->reference_id);
                if ($expense) {
                    if ($expense->expense_account_id) {
                        $expenseAccount = ChartOfAccount::find($expense->expense_account_id);
                        if ($expenseAccount) {
                            $description = $expenseAccount->name;
                        }
                    }
                    $notes = $expense->notes ?? '';
                    $reference = $expense->reference ?? '';
                }
            }

            $rows[] = [
                'date' => $row->date,
                'type' => ucfirst(str_replace('_', ' ', $type ?? '')),
                'reference' => $reference,
                'description' => $description ?: $row->description,
                'debit' => number_format($debit, 2),
                'credit' => number_format($credit, 2),
                'balance' => number_format($balance, 2),
                'created_at' => isset($row->created_at) ? (new \Carbon\Carbon($row->created_at))->format('ymdHi') : '',
                'notes' => $notes,
                'customer_id' => $customer_id,
                'vendor_id' => $vendor_id,
            ];
        }

        $entries->setCollection(collect($rows));
        
        $viewData = compact('bank', 'from', 'to', 'entries');
        if (!is_null($openingBalance)) {
            $viewData['openingBalance'] = $openingBalance;
        }

        if ($request->ajax()) {
            $html = view('banks._ledger_table', $viewData)->render();
            return response()->json(['html' => $html]);
        }
        
        return view('banks.ledger', $viewData);
    }

    protected function getOpeningBalance(Bank $bank)
    {
        // Get the bank's opening balance from database
        return $bank->opening_balance;
    }
    
    protected function getCurrentBalance(Bank $bank)
    {
        // Calculate current balance: opening + receipts - payments
        $opening = $this->getOpeningBalance($bank);
        $receipts = CustomerReceipt::where('payment_account_id', $bank->id)->sum('amount_received');
        $payments = VendorPayment::where('payment_account_id', $bank->id)->sum('amount');
        
        // Net transfers (incoming - outgoing)
        $incomingTransfers = BankTransfer::where('to_bank_id', $bank->id)->sum('amount');
        $outgoingTransfers = BankTransfer::where('from_bank_id', $bank->id)->sum('amount');
        
        return $opening + $receipts + $incomingTransfers - $payments - $outgoingTransfers;
    }

    public function exportPdf($bankId, Request $request)
    {
        $bank = Bank::findOrFail($bankId);
        $from = $request->input('from');
        $to = $request->input('to');

        $accountId = $bank->account_id;
        $query = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entry_lines.account_id', $accountId);
            
        if ($from) $query->where('journal_entries.date', '>=', $from);
        if ($to) $query->where('journal_entries.date', '<=', $to);

        $entries = $query->orderBy('journal_entries.date')
            ->select(
                'journal_entries.date',
                'journal_entries.description',
                'journal_entries.entry_number',
                'journal_entries.reference_type',
                'journal_entries.reference_id',
                'journal_entry_lines.debit',
                'journal_entry_lines.credit',
                'journal_entries.created_at'
            )->get();

        $balance = 0;
        $processedRows = [];
        
        // Calculate opening balance if date filter is applied
        if ($from) {
            $openingBalance = $this->calculateOpeningBalance($accountId, $from);
            $balance = $openingBalance;
        }

        foreach ($entries as $row) {
            $debit = $row->debit ?? 0;
            $credit = $row->credit ?? 0;
            $balance += ($debit - $credit);

            // Show related customer/vendor account name in description
            $description = '';
            $reference = '';
            $notes = '';
            $type = $row->reference_type;

            // Normalize type names
            if ($type === 'customer_receipt' || $type === 'CustomerReceipt') {
                $type = 'Receipt';
                $receipt = CustomerReceipt::find($row->reference_id);
                if ($receipt && $receipt->customer_id) {
                    $customer = Customer::find($receipt->customer_id);
                    if ($customer) {
                        $description = $customer->name;
                    }
                    $notes = $receipt->notes ?? '';
                    $reference = $receipt->reference ?? '';
                }
            } elseif ($type === 'vendor_payment') {
                $type = 'Payment';
                $payment = VendorPayment::find($row->reference_id);
                if ($payment && $payment->vendor_id) {
                    $vendor = Vendor::find($payment->vendor_id);
                    if ($vendor) {
                        $description = $vendor->name;
                    }
                    $notes = $payment->notes ?? '';
                    $reference = $payment->reference ?? '';
                }
            } elseif ($type === 'expense') {
                $type = 'Expense';
                $expense = \App\Models\Expense::find($row->reference_id);
                if ($expense) {
                    if ($expense->expense_account_id) {
                        $expenseAccount = \App\Models\ChartOfAccount::find($expense->expense_account_id);
                        if ($expenseAccount) {
                            $description = $expenseAccount->name;
                        }
                    }
                    $notes = $expense->notes ?? '';
                    $reference = $expense->reference ?? '';
                }
            } elseif ($type === 'bank') {
                $type = 'Bank';
            }

            $processedRows[] = [
                'date' => $row->date,
                'type' => $type,
                'description' => $description ?: $row->description,
                'reference' => $reference,
                'notes' => $notes,
                'debit' => number_format($debit, 2),
                'credit' => number_format($credit, 2),
                'balance' => number_format($balance, 2),
                'created_at' => $row->created_at ? (new \Carbon\Carbon($row->created_at))->format('ymdHi') : ''
            ];
        }

        $pdf = \PDF::loadView('banks.ledger_pdf', [
            'bank' => $bank,
            'entries' => $processedRows,
            'from' => $from,
            'to' => $to,
            'openingBalance' => $openingBalance ?? null
        ]);

        return $pdf->download('bank_ledger_' . $bank->id . '_' . date('Y-m-d') . '.pdf');
    }

    private function calculateOpeningBalance($accountId, $fromDate)
    {
        $query = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entry_lines.account_id', $accountId)
            ->where('journal_entries.date', '<', $fromDate)
            ->select('journal_entry_lines.debit', 'journal_entry_lines.credit');

        $openingBalance = 0;
        foreach ($query->get() as $entry) {
            $openingBalance += ($entry->debit ?? 0) - ($entry->credit ?? 0);
        }
        return $openingBalance;
    }
}
