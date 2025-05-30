@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-6 px-4">
    <h1 class="text-2xl font-bold mb-6">Daily Book</h1>

    <!-- Date Filter Form -->
    <form method="get" class="mb-6 bg-white rounded-lg shadow-sm p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date', date('Y-m-d')) }}" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ request('to_date', date('Y-m-d')) }}" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-md text-sm transition-colors">
                    Filter
                </button>
            </div>
        </div>
    </form>

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transactions as $transaction)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">
                            {{ $transaction->date->format('d M Y, g:i A') }}
                        </td>
                        <td class="px-4 py-3">
                            <span @class([
                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                'bg-green-100 text-green-800' => in_array($transaction->type, ['sale', 'receipt']),
                                'bg-red-100 text-red-800' => in_array($transaction->type, ['purchase', 'payment', 'expense']),
                            ])>
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $transaction->reference }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $transaction->description }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($transaction->amount, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-center">
                            <a href="{{ $transaction->url }}" class="text-blue-600 hover:text-blue-800 hover:underline">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            No transactions found for the selected date range.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $transactions->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
