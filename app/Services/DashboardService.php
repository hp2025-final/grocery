<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Sale;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\CustomerReceipt;
use App\Models\VendorPayment;
use App\Models\Product;
use App\Models\ChartOfAccount;

class DashboardService
{
    public function getKPIs($period = 'today')
    {
        $now = Carbon::now();
        if ($period === 'today') {
            $from = $now->copy()->startOfDay();
            $to = $now->copy()->endOfDay();
        } elseif ($period === 'week') {
            $from = $now->copy()->startOfWeek();
            $to = $now->copy()->endOfWeek();
        } else { // month
            $from = $now->copy()->startOfMonth();
            $to = $now->copy()->endOfMonth();
        }
        
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();
        // Total Sales (PKR)
        $totalSales = Sale::whereBetween('created_at', [$from, $to])->sum('total_amount');
        // Total Purchases (PKR)
        $totalPurchases = Purchase::whereBetween('created_at', [$from, $to])->sum('total_amount');
        // Total Expenses (PKR)
        $totalExpenses = Expense::whereBetween('created_at', [$from, $to])->sum('amount');
        // Total Customer Receipts (PKR)
        $totalReceipts = CustomerReceipt::whereBetween('created_at', [$from, $to])->sum('amount_received');
        // Total Vendor Payments (PKR)
        $totalPayments = VendorPayment::whereBetween('created_at', [$from, $to])->sum('amount_paid');

        // Cash in Hand
        $cashAccountIds = ChartOfAccount::where('type', 'Asset')->where('name', 'like', '%cash%')->pluck('id');
        $cashInHand = JournalEntryLine::whereIn('account_id', $cashAccountIds)->sum(DB::raw('debit - credit'));

        // Bank Balance
        $bankAccountIds = ChartOfAccount::where('type', 'Asset')->where('name', 'like', '%bank%')->pluck('id');
        $bankBalance = JournalEntryLine::whereIn('account_id', $bankAccountIds)->sum(DB::raw('debit - credit'));

        // Inventory Value
        $inventoryValue = Product::sum(DB::raw('opening_quantity * opening_rate'));

        // Net Profit (Current Month)
        $incomeAccountIds = ChartOfAccount::where('type', 'Income')->pluck('id');
        $expenseAccountIds = ChartOfAccount::where('type', 'Expense')->pluck('id');
        $income = JournalEntryLine::whereIn('account_id', $incomeAccountIds)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum(DB::raw('credit - debit'));
        $expenses = JournalEntryLine::whereIn('account_id', $expenseAccountIds)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum(DB::raw('debit - credit'));
        $netProfit = $income - $expenses;

        return [
            'sales' => $totalSales,
            'purchase' => $totalPurchases,
            'expense' => $totalExpenses,
            'receipt' => $totalReceipts,
            'payment' => $totalPayments,
        ];
    }

    // Returns array for sale chart (per hour/day)
    // Returns last 30 journal entries, paginated, filtered by period
    public function getRecentJournalEntries($period = 'today', $page = 1)
    {
        $now = Carbon::now();
        if ($period === 'today') {
            $from = $now->copy()->startOfDay();
            $to = $now->copy()->endOfDay();
        } elseif ($period === 'week') {
            $from = $now->copy()->startOfWeek();
            $to = $now->copy()->endOfWeek();
        } else {
            $from = $now->copy()->startOfMonth();
            $to = $now->copy()->endOfMonth();
        }
        $perPage = 10;
        $query = JournalEntry::whereBetween('date', [$from, $to])
            ->orderByDesc('date')
            ->orderByDesc('id');
        $total = $query->count();
        $entries = $query->take(30)->get(['id', 'date', 'entry_number', 'description', 'reference_type']);
        $paginated = $entries->slice(($page-1)*$perPage, $perPage)->values();
        return [
            'entries' => $paginated,
            'hasMore' => ($page * $perPage) < min($total, 30),
            'page' => $page
        ];
    }

    // Returns array for sale chart (per hour/day)
    public function getSaleChartData($period = 'today')
    {
        $now = Carbon::now();
        if ($period === 'today') {
            $from = $now->copy()->startOfDay();
            $to = $now->copy()->endOfDay();
            $sales = Sale::whereBetween('created_at', [$from, $to])
                ->get(['created_at', 'total_amount']);
            $data = array_fill(0, 24, 0);
            foreach ($sales as $sale) {
                $hour = Carbon::parse($sale->created_at)->hour;
                $data[$hour] += $sale->total_amount;
            }
            return $data;
        } elseif ($period === 'week') {
            $from = $now->copy()->startOfWeek();
            $to = $now->copy()->endOfWeek();
            $sales = Sale::whereBetween('created_at', [$from, $to])
                ->get(['created_at', 'total_amount']);
            $data = array_fill(0, 7, 0);
            foreach ($sales as $sale) {
                $day = Carbon::parse($sale->created_at)->dayOfWeek; // 0=Sun, 6=Sat
                $data[$day] += $sale->total_amount;
            }
            return $data;
        } else { // month
            $from = $now->copy()->startOfMonth();
            $to = $now->copy()->endOfMonth();
            $daysInMonth = $now->daysInMonth;
            $sales = Sale::whereBetween('created_at', [$from, $to])
                ->get(['created_at', 'total_amount']);
            $data = array_fill(0, $daysInMonth, 0);
            foreach ($sales as $sale) {
                $day = Carbon::parse($sale->created_at)->day - 1; // 0-based index
                $data[$day] += $sale->total_amount;
            }
            return $data;
        }
    }
}
