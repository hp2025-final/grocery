<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\CustomerReceipt;
use App\Models\VendorPayment;
use App\Models\Expense;
use App\Models\BankTransfer;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DailyBookController extends Controller
{
    public function index(Request $request)
    {
        $fromDate = $request->input('from_date', Carbon::now()->format('Y-m-d'));
        $toDate = $request->input('to_date', Carbon::now()->format('Y-m-d'));

        $transactions = collect();

        // Get Sales
        $sales = Sale::whereBetween('sale_date', [$fromDate, $toDate])
            ->with(['customer', 'items.product'])
            ->get()
            ->each(function($sale) use (&$transactions) {                $transactions->push([
                    'date' => Carbon::parse($sale->sale_date),
                    'type' => 'Sale',
                    'number' => $sale->sale_number,
                    'description' => 'Sale to ' . $sale->customer->name,
                    'party' => $sale->customer->name,
                    'amount' => $sale->net_amount,
                    'model' => $sale
                ]);
            });

        // Get Purchases
        $purchases = Purchase::whereBetween('purchase_date', [$fromDate, $toDate])
            ->with(['vendor', 'items.product'])
            ->get()
            ->each(function($purchase) use (&$transactions) {                $transactions->push([
                    'date' => Carbon::parse($purchase->purchase_date),
                    'type' => 'Purchase',
                    'number' => $purchase->purchase_number,
                    'description' => 'Purchase from ' . $purchase->vendor->name,
                    'party' => $purchase->vendor->name,
                    'amount' => $purchase->net_amount,
                    'model' => $purchase
                ]);
            });

        // Get Customer Receipts
        $receipts = CustomerReceipt::whereBetween('receipt_date', [$fromDate, $toDate])
            ->with('customer')
            ->get()
            ->each(function($receipt) use (&$transactions) {                $transactions->push([
                    'date' => Carbon::parse($receipt->receipt_date),
                    'type' => 'Receipt',
                    'number' => $receipt->receipt_number,
                    'description' => 'Receipt from ' . $receipt->customer->name,
                    'party' => $receipt->customer->name,
                    'amount' => $receipt->amount_received,
                    'model' => $receipt
                ]);
            });

        // Get Vendor Payments
        $payments = VendorPayment::whereBetween('payment_date', [$fromDate, $toDate])
            ->with('vendor')
            ->get()
            ->each(function($payment) use (&$transactions) {                $transactions->push([
                    'date' => Carbon::parse($payment->payment_date),
                    'type' => 'Payment',
                    'number' => $payment->payment_number,
                    'description' => 'Payment to ' . $payment->vendor->name,
                    'party' => $payment->vendor->name,
                    'amount' => $payment->amount_paid,
                    'model' => $payment
                ]);
            });

        // Get Expenses
        $expenses = Expense::whereBetween('expense_date', [$fromDate, $toDate])
            ->with('expenseAccount')
            ->get()
            ->each(function($expense) use (&$transactions) {                $transactions->push([
                    'date' => Carbon::parse($expense->expense_date),
                    'type' => 'Expense',
                    'number' => $expense->voucher_number,
                    'description' => $expense->description . ' (' . $expense->expenseAccount->name . ')',
                    'party' => $expense->expenseAccount->name,
                    'amount' => $expense->amount,
                    'model' => $expense
                ]);
            });        // Get Bank Transfers
        $bankTransfers = BankTransfer::whereBetween('date', [$fromDate, $toDate])
            ->with(['fromBank', 'toBank'])
            ->get()
            ->each(function($transfer) use (&$transactions) {                $transactions->push([
                    'date' => Carbon::parse($transfer->date),
                    'type' => 'Bank Transfer',
                    'number' => $transfer->transfer_number,
                    'description' => 'Transfer from ' . $transfer->fromBank->name . ' to ' . $transfer->toBank->name,
                    'party' => $transfer->fromBank->name . ' → ' . $transfer->toBank->name,
                    'amount' => $transfer->amount,
                    'model' => $transfer
                ]);
            });

        $transactions = $transactions->sortByDesc('date');

        return view('reports.daily-book', compact('transactions', 'fromDate', 'toDate'));
    }
}
