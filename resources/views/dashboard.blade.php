@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4" x-data="dashboard()">
    <!-- Filter Buttons -->
    <div class="flex flex-wrap gap-1 justify-end mb-4">
        <button @click="setPeriod('today')" :class="period==='today' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'bg-white border-gray-300 text-gray-500'" class="text-xs px-2 py-1 border rounded transition">Today</button>
        <button @click="setPeriod('week')" :class="period==='week' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'bg-white border-gray-300 text-gray-500'" class="text-xs px-2 py-1 border rounded transition">This Week</button>
        <button @click="setPeriod('month')" :class="period==='month' ? 'bg-blue-50 border-blue-500 text-blue-700' : 'bg-white border-gray-300 text-gray-500'" class="text-xs px-2 py-1 border rounded transition">This Month</button>
    </div>
    <!-- KPI Cards - Redesigned like reference screenshot -->
    <div class="bg-white rounded-xl shadow border border-gray-200 mb-6">
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
    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
        <div class="bg-white rounded-xl p-6 shadow flex flex-col items-center min-h-[300px]">
            <h3 class="text-lg font-semibold mb-4">Sale Chart</h3>
            <template x-if="saleChartData.length > 0">
                <div class="w-full h-56 flex items-end justify-between">
                    <svg :width="saleChartData.length * 18" height="200" class="block mx-auto">
                        <template x-for="(val, idx) in saleChartData" :key="idx">
                            <rect :x="idx * 18" :y="200 - (val/maxSale*180)" :width="14" :height="(val/maxSale*180)" rx="3" :fill="'#22292f'" />
                        </template>
                    </svg>
                </div>
            </template>
            <template x-if="saleChartData.length === 0">
                <div class="w-full h-56 flex items-center justify-center text-gray-400">No data</div>
            </template>
        </div>
        <div class="bg-white rounded-xl p-6 shadow flex flex-col items-center min-h-[300px]">
            <h3 class="text-lg font-semibold mb-4">Top Selling Product Chart</h3>
            <div class="w-full h-56 flex items-center justify-center text-gray-400">[Top Selling Product Chart]</div>
        </div>
    </div>
    <!-- Recent Entries Table -->
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold">Recent Journal Entries</h2>
            <template x-if="loading"><span class="text-sm text-gray-400 ml-2">Loading...</span></template>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">Date</th>
                        <th class="px-4 py-2 text-left">Entry #</th>
                        <th class="px-4 py-2 text-left">Description</th>
                        <th class="px-4 py-2 text-left">Reference</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="entry in entries" :key="entry.id">
                        <tr>
                            <td class="px-4 py-2" x-text="entry.date"></td>
                            <td class="px-4 py-2" x-text="entry.entry_number"></td>
                            <td class="px-4 py-2" x-text="entry.description"></td>
                            <td class="px-4 py-2" x-text="entry.reference_type"></td>
                        </tr>
                    </template>
                    <template x-if="entries.length === 0 && !loading">
                        <tr><td colspan="4" class="px-4 py-2 text-center text-gray-400">No entries found.</td></tr>
                    </template>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="flex justify-end mt-4 space-x-2">
            <button @click="prevPage" :disabled="page === 1" class="px-3 py-1 rounded bg-gray-200 text-gray-600 hover:bg-gray-300 disabled:opacity-50">Prev</button>
            <span class="px-2 py-1">Page <span x-text="page"></span></span>
            <button @click="nextPage" :disabled="!hasMore" class="px-3 py-1 rounded bg-gray-200 text-gray-600 hover:bg-gray-300 disabled:opacity-50">Next</button>
        </div>
    </div>
    <!-- Alpine.js Dashboard Script -->
    <script>
    function dashboard() {
        return {
            period: 'today',
            kpi: {sales: 0, receipt: 0, purchase: 0, payment: 0, expense: 0},
            trend: {sales: 0, receipt: 0, purchase: 0, payment: 0, expense: 0}, // Placeholder for future trend
            saleChartData: [],
            maxSale: 1,
            entries: [],
            page: 1,
            hasMore: false,
            loading: false,
            setPeriod(p) {
                this.period = p;
                this.page = 1;
                this.fetchKPIs();
                this.fetchEntries();
                this.fetchSaleChartData();
            },
            prevPage() {
                if (this.page > 1) { this.page--; this.fetchEntries(); }
            },
            nextPage() {
                if (this.hasMore) { this.page++; this.fetchEntries(); }
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
            fetchEntries() {
                this.loading = true;
                fetch(`/dashboard/journal-entries?page=${this.page}&period=${this.period}`)
                    .then(r => r.json())
                    .then(data => {
                        this.entries = data.entries;
                        this.hasMore = data.hasMore;
                        this.loading = false;
                    });
            },
            init() {
                this.fetchKPIs();
                this.fetchEntries();
                this.fetchSaleChartData();
            },
            fetchSaleChartData() {
                console.log('Fetching sale chart data for period:', this.period);
                fetch(`/dashboard/sale-chart?period=${this.period}`)
                    .then(r => {
                        if (!r.ok) throw new Error(`HTTP error! status: ${r.status}`);
                        return r.json();
                    })
                    .then(data => {
                        console.log('Sale chart data:', data);
                        this.saleChartData = data;
                        this.maxSale = Math.max(...data.map(d => d.amount), 1);
                    })
                    .catch(error => {
                        console.error('Error fetching sale chart data:', error);
                    });
            }
        }
    }
    document.addEventListener('alpine:init', () => { Alpine.data('dashboard', dashboard); });
    </script>
</div>
@endsection

