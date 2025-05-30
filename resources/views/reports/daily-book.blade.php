@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 px-4">
    <h1 class="text-2xl font-bold mb-6">Daily Book</h1>

    <!-- Date Filter Form -->
    <form method="get" class="mb-6 bg-white rounded-lg shadow-sm p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ $fromDate }}" 
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ $toDate }}" 
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-colors duration-200">
                    Filter
                </button>
            </div>
        </div>
    </form>    <!-- Sections -->
    <div class="space-y-6">
        @php 
            $sections = [
                'Sales' => [
                    'transactions' => $transactions->where('type', 'Sale'),
                    'color' => 'green',
                    'title' => 'Sales',
                    'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>'
                ],
                'Purchases' => [
                    'transactions' => $transactions->where('type', 'Purchase'),
                    'color' => 'blue',
                    'title' => 'Purchases',
                    'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>'
                ],
                'Customer Receipts' => [
                    'transactions' => $transactions->where('type', 'Receipt'),
                    'color' => 'yellow',
                    'title' => 'Customer Receipts',
                    'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>'
                ],
                'Vendor Payments' => [
                    'transactions' => $transactions->where('type', 'Payment'),
                    'color' => 'purple',
                    'title' => 'Vendor Payments',
                    'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
                ],
                'Expenses' => [
                    'transactions' => $transactions->where('type', 'Expense'),
                    'color' => 'red',
                    'title' => 'Expenses',
                    'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>'
                ],
                'Bank Transfers' => [
                    'transactions' => $transactions->where('type', 'Bank Transfer'),
                    'color' => 'gray',
                    'title' => 'Bank Transfers',
                    'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>'
                ]
            ];
        @endphp

        @foreach($sections as $key => $section)
            @if($section['transactions']->count())
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="bg-{{ $section['color'] }}-50 px-4 py-2 border-b border-gray-200">
                        <div class="flex items-center space-x-2">
                            <span class="text-{{ $section['color'] }}-600">{!! $section['icon'] !!}</span>
                            <h2 class="text-lg font-semibold text-gray-800">{{ $section['title'] }}</h2>
                            <span class="ml-auto text-sm font-medium text-{{ $section['color'] }}-600">
                                Total: {{ number_format($section['transactions']->sum('amount'), 2) }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600">Total: {{ number_format($section['transactions']->sum('amount'), 2) }}</p>
                    </div>
                    @include('reports.daily-book._transactions_table', ['transactions' => $section['transactions']])
                </div>
            @endif        @endforeach

        @if($transactions->isEmpty())
        <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
            No transactions found for the selected date range
        </div>
        @endif
    </div>
</div>
@endsection
