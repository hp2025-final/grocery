<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;

class CustomerLedgerController extends Controller
{
    public function show($customerId, Request $request)
    {
        $customer = \App\Models\Customer::findOrFail($customerId);

        // Get date filters with defaults
        $from = $request->input('from', '2025-01-01');
        $to = $request->input('to', date('Y-m-d'));

        // Get all sales for this customer
        $saleIds = \App\Models\Sale::where('customer_id', $customerId)->pluck('id')->toArray();
        $receiptIds = $customer->receipts()->pluck('id')->toArray();

        // Fetch all relevant journal entries for this customer
        $query = JournalEntry::where(function ($q) use ($customerId, $saleIds, $receiptIds) {
            $q->where(function ($q2) use ($customerId) {
                $q2->where('reference_type', 'customer')
                   ->where('reference_id', $customerId);
            })
            ->orWhere(function ($q2) use ($saleIds) {
                if (!empty($saleIds)) {
                    $q2->where('reference_type', 'sale')
                       ->whereIn('reference_id', $saleIds);
                }
            })
            ->orWhere(function ($q2) use ($receiptIds) {
                if (!empty($receiptIds)) {
                    $q2->where('reference_type', 'customer_receipt')
                       ->whereIn('reference_id', $receiptIds);
                }
            });
        });
        if ($from) $query->whereDate('date', '>=', $from);
        if ($to) $query->whereDate('date', '<=', $to);
        $entries = $query->orderBy('date')->with('lines')->get();

        // Build ledger rows (sales, receipts, opening, etc.)
        $rows = [];
        $balance = 0;

        // Opening balance logic
        if ($from) {
            // Calculate sum of all debits - credits before $from for this customer account
            $openingQuery = JournalEntry::where(function ($q) use ($customerId, $saleIds, $receiptIds) {
                $q->where(function ($q2) use ($customerId) {
                    $q2->where('reference_type', 'customer')
                        ->where('reference_id', $customerId);
                })
                ->orWhere(function ($q2) use ($saleIds) {
                    if (!empty($saleIds)) {
                        $q2->where('reference_type', 'sale')
                            ->whereIn('reference_id', $saleIds);
                    }
                })
                ->orWhere(function ($q2) use ($receiptIds) {
                    if (!empty($receiptIds)) {
                        $q2->where('reference_type', 'customer_receipt')
                            ->whereIn('reference_id', $receiptIds);
                    }
                });
            })
            ->whereDate('date', '<', $from)
            ->with('lines')
            ->get();

            $openingBalance = 0;
            foreach ($openingQuery as $entry) {
                foreach ($entry->lines as $line) {
                    if ($line->account_id == $customer->account_id) {
                        $openingBalance += ($line->debit ?? 0) - ($line->credit ?? 0);
                    }
                }
            }
            $rows[] = [
                'date' => $from,
                'type' => 'Opening balance',
                
                'debit' => 0,
                'credit' => 0,
                'balance' => $openingBalance,
                'description' => '',
                'sale_items' => [],
                'receipt_bank' => null,
                'receipt_account_title' => null,
                'receipt_reference' => null,
            ];
            $balance = $openingBalance;
        }

        foreach ($entries as $entry) {
            foreach ($entry->lines as $line) {
                // Only include lines for this customer's dedicated account
                if ($line->account_id != $customer->account_id) continue;
                // For sales, ensure reference_type is exactly 'sale'
                if ($entry->reference_type === 'sale' && $line->account_id != $customer->account_id) continue;
                $debit = $line->debit ?? 0;
                $credit = $line->credit ?? 0;
                $balance += $debit - $credit;
                $saleItems = [];
if ($entry->reference_type === 'sale') {
    $sale = \App\Models\Sale::with(['items.product', 'items.unit'])->find($entry->reference_id);
    if ($sale) {
        foreach ($sale->items as $item) {
            $saleItems[] = [
                'product' => $item->product->name ?? '',
                'qty' => $item->quantity,
                'unit' => $item->unit->abbreviation ?? '',
                'rate' => $item->rate,
                'total' => $item->total_amount,
            ];
        }
        if ($sale->discount_amount > 0) {
            $saleItems[] = [
                'product' => 'Discount Allowed',
                'qty' => '',
                'unit' => '',
                'rate' => '',
                'total' => $sale->discount_amount,
            ];
        }
    }
}
$receiptBank = null;
$receiptAccountTitle = null;
$receiptReference = null;
if ($entry->reference_type === 'customer_receipt') {
    $receipt = \App\Models\CustomerReceipt::find($entry->reference_id);
    if ($receipt) {
        if ($receipt->payment_account_id) {
            $account = \App\Models\ChartOfAccount::find($receipt->payment_account_id);
            if ($account) {
                $receiptBank = $account->name;
                $receiptAccountTitle = $account->code;
            }
        }
        $receiptReference = $receipt->reference;
    }
}
$detail = '';
if ($entry->reference_type === 'customer_receipt') {
    $receipt = \App\Models\CustomerReceipt::find($entry->reference_id);
    if ($receipt) {
        $detail = $receipt->reference;
    }
}
$notes = '';
if ($entry->reference_type === 'customer_receipt') {
    $receipt = \App\Models\CustomerReceipt::find($entry->reference_id);
    if ($receipt) {
        $notes = $receipt->notes;
    }
} elseif ($entry->reference_type === 'sale') {
    $sale = \App\Models\Sale::find($entry->reference_id);
    if ($sale) {
        $notes = $sale->notes;
    }
}
$rows[] = [
    'date' => $entry->date,
    'type' => ($entry->reference_type === 'sale' ? 'Sale' : ($entry->reference_type === 'customer_receipt' ? 'Receipt' : ($entry->description ?? $entry->reference_type))),
    
    'debit' => $debit,
    'credit' => $credit,
    'balance' => $balance,
    'accounts' => $line->description,
    'reference' => $entry->entry_number ?? '',
    'description' => $entry->description ?? '',
    'created_at' => isset($entry->created_at) ? (new \Carbon\Carbon($entry->created_at))->format('ymdHi') : '',
    'detail' => $detail,
    'notes' => $notes,
    'sale_items' => $saleItems,
    'receipt_bank' => $receiptBank,
    'receipt_account_title' => $receiptAccountTitle,
    'receipt_reference' => $receiptReference,
];
            }
        }

        return view('customer_ledgers.show', compact('customer', 'rows', 'from', 'to'));
    }

    public function exportPdf($customerId, Request $request)
    {
        $customer = Customer::findOrFail($customerId);
        $from = $request->input('from');
        $to = $request->input('to');

        // Reuse the same logic from show() method to get the data
        $saleIds = $customer->sales()->pluck('id')->toArray();
        $receiptIds = $customer->receipts()->pluck('id')->toArray();

        $query = JournalEntry::where(function ($q) use ($customerId, $saleIds, $receiptIds) {
            $q->where(function ($q2) use ($customerId) {
                $q2->where('reference_type', 'customer')
                   ->where('reference_id', $customerId);
            })
            ->orWhere(function ($q2) use ($saleIds) {
                if (!empty($saleIds)) {
                    $q2->where('reference_type', 'sale')
                       ->whereIn('reference_id', $saleIds);
                }
            })
            ->orWhere(function ($q2) use ($receiptIds) {
                if (!empty($receiptIds)) {
                    $q2->where('reference_type', 'customer_receipt')
                       ->whereIn('reference_id', $receiptIds);
                }
            });
        });

        if ($from) $query->whereDate('date', '>=', $from);
        if ($to) $query->whereDate('date', '<=', $to);

        $entries = $query->orderBy('date')->with('lines')->get();

        // Build ledger rows (sales, receipts, opening, etc.)
        $rows = [];
        $balance = 0;

        // Opening balance logic
        if ($from) {
            $openingQuery = JournalEntry::where(function ($q) use ($customerId, $saleIds, $receiptIds) {
                $q->where(function ($q2) use ($customerId) {
                    $q2->where('reference_type', 'customer')
                        ->where('reference_id', $customerId);
                })
                ->orWhere(function ($q2) use ($saleIds) {
                    if (!empty($saleIds)) {
                        $q2->where('reference_type', 'sale')
                            ->whereIn('reference_id', $saleIds);
                    }
                })
                ->orWhere(function ($q2) use ($receiptIds) {
                    if (!empty($receiptIds)) {
                        $q2->where('reference_type', 'customer_receipt')
                            ->whereIn('reference_id', $receiptIds);
                    }
                });
            })
            ->whereDate('date', '<', $from)
            ->with('lines')
            ->get();

            $openingBalance = 0;
            foreach ($openingQuery as $entry) {
                foreach ($entry->lines as $line) {
                    if ($line->account_id == $customer->account_id) {
                        $openingBalance += ($line->debit ?? 0) - ($line->credit ?? 0);
                    }
                }
            }
            $rows[] = [
                'date' => $from,
                'type' => 'Opening balance',
                'debit' => 0,
                'credit' => 0,
                'balance' => $openingBalance,
                'description' => '',
                'sale_items' => [],
                'receipt_bank' => null,
                'receipt_account_title' => null,
                'receipt_reference' => null,
                'notes' => '',
            ];
            $balance = $openingBalance;
        }

        foreach ($entries as $entry) {
            foreach ($entry->lines as $line) {
                if ($line->account_id != $customer->account_id) continue;
                if ($entry->reference_type === 'sale' && $line->account_id != $customer->account_id) continue;

                $debit = $line->debit ?? 0;
                $credit = $line->credit ?? 0;
                $balance += $debit - $credit;

                $saleItems = [];
                $receiptBank = null;
                $receiptAccountTitle = null;
                $receiptReference = null;

                if ($entry->reference_type === 'sale') {
                    $sale = \App\Models\Sale::with(['items.product', 'items.unit'])->find($entry->reference_id);
                    if ($sale) {
                        foreach ($sale->items as $item) {
                            $saleItems[] = [
                                'product' => $item->product->name ?? '',
                                'qty' => $item->quantity,
                                'unit' => $item->unit->abbreviation ?? '',
                                'rate' => $item->rate,
                                'total' => $item->total_amount,
                            ];
                        }
                        if ($sale->discount_amount > 0) {
                            $saleItems[] = [
                                'product' => 'Discount Allowed',
                                'qty' => '',
                                'unit' => '',
                                'rate' => '',
                                'total' => $sale->discount_amount,
                            ];
                        }
                    }
                }

                if ($entry->reference_type === 'customer_receipt') {
                    $receipt = \App\Models\CustomerReceipt::find($entry->reference_id);
                    if ($receipt) {
                        if ($receipt->payment_account_id) {
                            $account = \App\Models\ChartOfAccount::find($receipt->payment_account_id);
                            if ($account) {
                                $receiptBank = $account->name;
                                $receiptAccountTitle = $account->code;
                            }
                        }
                        $receiptReference = $receipt->reference;
                    }
                }

                $notes = '';
                if ($entry->reference_type === 'customer_receipt') {
                    $receipt = \App\Models\CustomerReceipt::find($entry->reference_id);
                    if ($receipt) {
                        $notes = $receipt->notes;
                    }
                } elseif ($entry->reference_type === 'sale') {
                    $sale = \App\Models\Sale::find($entry->reference_id);
                    if ($sale) {
                        $notes = $sale->notes;
                    }
                }

                $rows[] = [
                    'date' => $entry->date,
                    'type' => ($entry->reference_type === 'sale' ? 'Sale' : ($entry->reference_type === 'customer_receipt' ? 'Receipt' : ($entry->description ?? $entry->reference_type))),
                    'debit' => $debit,
                    'credit' => $credit,
                    'balance' => $balance,
                    'description' => $entry->description ?? '',
                    'notes' => $notes,
                    'sale_items' => $saleItems,
                    'receipt_bank' => $receiptBank,
                    'receipt_account_title' => $receiptAccountTitle,
                    'receipt_reference' => $receiptReference,
                ];
            }
        }

        $pdf = \PDF::loadView('customer_ledgers.ledger_pdf', [
            'customer' => $customer,
            'rows' => $rows,
            'from' => $from,
            'to' => $to
        ]);

        return $pdf->download('customer_ledger_' . $customer->id . '_' . date('Y-m-d') . '.pdf');
    }
}
