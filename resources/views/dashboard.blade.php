@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4" x-data="dashboard()">
    <!-- Filter Buttons -->
    <div class="flex flex-wrap gap-1 justify-end mb-4">
        <button @click="setPeriod('today')" :class="period==='today' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'bg-white border-gray-300 text-gray-500'" class="text-xs px-2 py-1 border rounded transition">Today</button>
        <button @click="setPeriod('week')" :class="period==='week' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'bg-white border-gray-300 text-gray-500'" class="text-xs px-2 py-1 border rounded transition">This Week</button>
        <button @click="setPeriod('month')" :class="period==='month' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'bg-white border-gray-300 text-gray-500'" class="text-xs px-2 py-1 border rounded transition">This Month</button>
    </div>
    <!-- KPI Cards -->
    <div class="bg-white rounded-xl shadow border border-gray-200">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 divide-y sm:divide-y-0 sm:divide-x divide-gray-200">
            <div class="flex flex-col justify-between p-5 min-w-[160px]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500">Sales</span>
                    <span class="text-xs font-medium" :class="trend.sales > 0 ? 'text-green-600' : (trend.sales < 0 ? 'text-red-500' : 'text-gray-400')" x-text="(trend.sales > 0 ? '+' : '') + trend.sales.toFixed(2) + '%'">+0.00%</span>
                </div>
                <span class="text-2xl font-bold text-gray-900" x-text="kpi.sales">0</span>
            </div>
            <div class="flex flex-col justify-between p-5 min-w-[160px]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500">Receipt</span>
                    <span class="text-xs font-medium" :class="trend.receipt > 0 ? 'text-green-600' : (trend.receipt < 0 ? 'text-red-500' : 'text-gray-400')" x-text="(trend.receipt > 0 ? '+' : '') + trend.receipt.toFixed(2) + '%'">+0.00%</span>
                </div>
                <span class="text-2xl font-bold text-gray-900" x-text="kpi.receipt">0</span>
            </div>
            <div class="flex flex-col justify-between p-5 min-w-[160px]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500">Purchase</span>
                    <span class="text-xs font-medium" :class="trend.purchase > 0 ? 'text-green-600' : (trend.purchase < 0 ? 'text-red-500' : 'text-gray-400')" x-text="(trend.purchase > 0 ? '+' : '') + trend.purchase.toFixed(2) + '%'">+0.00%</span>
                </div>
                <span class="text-2xl font-bold text-gray-900" x-text="kpi.purchase">0</span>
            </div>
            <div class="flex flex-col justify-between p-5 min-w-[160px]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500">Payment</span>
                    <span class="text-xs font-medium" :class="trend.payment > 0 ? 'text-green-600' : (trend.payment < 0 ? 'text-red-500' : 'text-gray-400')" x-text="(trend.payment > 0 ? '+' : '') + trend.payment.toFixed(2) + '%'">+0.00%</span>
                </div>
                <span class="text-2xl font-bold text-gray-900" x-text="kpi.payment">0</span>
            </div>
            <div class="flex flex-col justify-between p-5 min-w-[160px]">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-gray-500">Expense</span>
                    <span class="text-xs font-medium" :class="trend.expense > 0 ? 'text-green-600' : (trend.expense < 0 ? 'text-red-500' : 'text-gray-400')" x-text="(trend.expense > 0 ? '+' : '') + trend.expense.toFixed(2) + '%'">+0.00%</span>
                </div>
                <span class="text-2xl font-bold text-gray-900" x-text="kpi.expense">0</span>
            </div>
        </div>
    </div>
    
    <!-- Quick Action Buttons -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mt-6">
        <!-- Add Sale -->
        <a href="{{ route('sales.create') }}" class="flex flex-col items-center justify-center p-4 bg-white rounded-lg shadow-sm border border-gray-200 hover:bg-gray-50 transition-colors">
            <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span class="text-sm font-medium text-gray-900">Add Sale</span>
        </a>
        
        <!-- Add Purchase -->
        <a href="{{ route('purchases.create') }}" class="flex flex-col items-center justify-center p-4 bg-white rounded-lg shadow-sm border border-gray-200 hover:bg-gray-50 transition-colors">
            <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span class="text-sm font-medium text-gray-900">Add Purchase</span>
        </a>

        <!-- Add Receipt -->
        <a href="{{ route('customer-receipts.create') }}" class="flex flex-col items-center justify-center p-4 bg-white rounded-lg shadow-sm border border-gray-200 hover:bg-gray-50 transition-colors">
            <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
            </svg>
            <span class="text-sm font-medium text-gray-900">Add Receipt</span>
        </a>

        <!-- Add Payment -->
        <a href="{{ route('vendor-payments.create') }}" class="flex flex-col items-center justify-center p-4 bg-white rounded-lg shadow-sm border border-gray-200 hover:bg-gray-50 transition-colors">
            <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            <span class="text-sm font-medium text-gray-900">Add Payment</span>
        </a>

        <!-- Add Expense -->
        <a href="{{ route('expenses.create') }}" class="flex flex-col items-center justify-center p-4 bg-white rounded-lg shadow-sm border border-gray-200 hover:bg-gray-50 transition-colors">
            <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
            </svg>
            <span class="text-sm font-medium text-gray-900">Add Expense</span>
        </a>

        <!-- Internal Funds Transfer -->
        <a href="{{ route('bank_transfers.create') }}" class="flex flex-col items-center justify-center p-4 bg-white rounded-lg shadow-sm border border-gray-200 hover:bg-gray-50 transition-colors">
            <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            <span class="text-sm font-medium text-gray-900">Internal Funds Transfer</span>
        </a>
    </div>

    <!-- Alpine.js Dashboard Script -->
    <script>
    function dashboard() {
        return {
            period: 'today',
            kpi: {sales: 0, receipt: 0, purchase: 0, payment: 0, expense: 0},
            trend: {sales: 0, receipt: 0, purchase: 0, payment: 0, expense: 0}, // Placeholder for future trend
            loading: false,
            setPeriod(p) {
                this.period = p;
                this.fetchKPIs();
            },
            fetchKPIs() {
                this.loading = true;
                console.log('Fetching KPIs for period:', this.period);
                fetch(`/dashboard/kpis?period=${this.period}`)
                    .then(r => {
                        if (!r.ok) throw new Error(`HTTP error! status: ${r.status}`);
                        return r.json();
                    })
                    .then(data => {
                        console.log('KPI data:', data);
                        this.kpi = data;
                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('Error fetching KPIs:', error);
                        this.loading = false;
                    });
            },
            init() {
                this.fetchKPIs();
            }
        }
    }
    document.addEventListener('alpine:init', () => { Alpine.data('dashboard', dashboard); });
    </script>
</div>
@endsection

