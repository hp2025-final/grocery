<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\Purchase;
use App\Models\VendorPayment;
use Illuminate\Support\Facades\DB;
use PDF;

class VendorLedgerController extends Controller
{
    public function show($vendorId, Request $request)
    {
        $vendor = \App\Models\Vendor::with('account')->findOrFail($vendorId);

        // Get date filters with defaults
        $from = $request->input('from', '2025-01-01');
        $to = $request->input('to', date('Y-m-d'));

        $rows = $this->getLedgerData($vendor, $from, $to);
        return view('vendors.ledger', compact('vendor', 'rows', 'from', 'to'));
    }

    public function exportPdf($vendorId, Request $request)
    {
        $vendor = Vendor::findOrFail($vendorId);
        $from = $request->input('from');
        $to = $request->input('to');

        $rows = $this->getLedgerData($vendor, $from, $to);
        
        $pdf = PDF::loadView('vendors.ledger_pdf', compact('vendor', 'rows', 'from', 'to'));
        return $pdf->download('vendor_ledger_' . $vendor->id . '.pdf');
    }

    private function getLedgerData($vendor, $from, $to)
    {
        $accountId = $vendor->account_id;
        $query = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entry_lines.account_id', $accountId)
            ->whereBetween('journal_entries.date', [$from, $to])
            ->orderBy('journal_entries.date')
            ->select(
                'journal_entries.date',
                'journal_entries.description',
                'journal_entries.entry_number',
                'journal_entries.reference_type',
                'journal_entries.reference_id',
                'journal_entry_lines.debit',
                'journal_entry_lines.credit'
            )
            ->get();

        $rows = [];
        $balance = 0;

        // Opening balance logic (if date filter applied)
        if ($from) {
            $openingQuery = DB::table('journal_entry_lines')
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_entry_lines.account_id', $accountId)
                ->where('journal_entries.date', '<', $from)
                ->select(
                    'journal_entry_lines.debit',
                    'journal_entry_lines.credit'
                )
                ->get();
            $openingBalance = 0;
            foreach ($openingQuery as $entry) {
                $openingBalance += ($entry->debit ?? 0) - ($entry->credit ?? 0);
            }
            $rows[] = [
                'date' => $from,
                'type' => 'Opening balance',
                'description' => '',
                'notes' => '',
                'debit' => '0',
                'credit' => '0',
                'balance' => number_format($openingBalance, 0),
                'created_at' => '',
                'details' => [],
                'discount' => null,
                'bank' => null,
                'account_title' => null,
                'payment_reference' => null,
            ];
            $balance = $openingBalance;
        }

        foreach ($query as $row) {
            $details = [];
            $discount = null;
            $bank = null;
            $accountTitle = null;
            $paymentReference = null;
            $debit = $row->debit ?? 0;
            $credit = $row->credit ?? 0;
            $balance += ($debit - $credit);

            // For purchase entries, fetch purchase items
            if ($row->reference_type === 'purchase') {
                $purchase = Purchase::with(['items.product', 'items.unit'])->find($row->reference_id);
                if ($purchase) {
                    foreach ($purchase->items as $item) {
                        $details[] = [
                            'product' => $item->product->name ?? '',
                            'qty' => $item->quantity,
                            'unit' => $item->unit->abbreviation ?? '',
                            'rate' => $item->rate,
                            'total' => $item->amount,
                        ];
                    }
                    if ($purchase->discount_amount > 0) {
                        $discount = $purchase->discount_amount;
                    }
                }
            }
            // For vendor payments, fetch bank/account info
            if ($row->reference_type === 'vendor_payment') {
                $payment = VendorPayment::find($row->reference_id);
                if ($payment && $payment->payment_account_id) {
                    $account = ChartOfAccount::find($payment->payment_account_id);
                    if ($account) {
                        $bank = $account->name;
                        $accountTitle = $account->code;
                    }
                }
                $paymentReference = $payment->reference ?? null;
            }
            $notes = '';
            if ($row->reference_type === 'vendor_payment') {
                $payment = VendorPayment::find($row->reference_id);
                if ($payment) {
                    $notes = $payment->notes;
                }
            } elseif ($row->reference_type === 'purchase') {
                $purchase = Purchase::find($row->reference_id);
                if ($purchase) {
                    $notes = $purchase->notes;
                }
            }
            $createdAt = '';
            if ($row->reference_type === 'vendor_payment') {
                $payment = VendorPayment::find($row->reference_id);
                if ($payment && $payment->created_at) {
                    $createdAt = $payment->created_at->format('ymdHi');
                }
            } elseif ($row->reference_type === 'purchase') {
                $purchase = Purchase::find($row->reference_id);
                if ($purchase && $purchase->created_at) {
                    $createdAt = $purchase->created_at->format('ymdHi');
                }
            }
            $rows[] = [
                'date' => $row->date,
                'type' => ($row->reference_type === 'vendor_payment' ? 'Payment' : ucfirst($row->reference_type ?? '')),
                'reference' => $row->entry_number,
                'description' => $row->description,
                'notes' => $notes,
                'created_at' => $createdAt,
                'debit' => is_numeric($debit) ? number_format($debit, 0) : '0',
                'credit' => is_numeric($credit) ? number_format($credit, 0) : '0',
                'balance' => number_format($balance, 0),
                'details' => $details,
                'discount' => $discount,
                'bank' => $bank,
                'account_title' => $accountTitle,
                'payment_reference' => $paymentReference,
            ];
        }

        return $rows;
    }
}
