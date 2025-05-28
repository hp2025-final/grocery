@extends('layouts.app')
@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4">
        <div class="mb-6">
            <h1 class="text-2xl font-bold">All Sale Invoices</h1>
        </div>

        <!-- Filters -->
        <form method="get" class="mb-6">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                        <div class="lg:col-span-1">
                            <label class="block text-xs font-medium text-gray-700 mb-1">From Date</label>
                            <input type="date" name="from_date" value="{{ request('from_date') }}" 
                                class="w-full h-9 rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div class="lg:col-span-1">
                            <label class="block text-xs font-medium text-gray-700 mb-1">To Date</label>
                            <input type="date" name="to_date" value="{{ request('to_date') }}" 
                                class="w-full h-9 rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div class="lg:col-span-1">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Customer</label>
                            <select name="customer_id" 
                                class="w-full h-9 rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500 px-2.5">
                                <option value="">All Customers</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" @if(request('customer_id')==$customer->id) selected @endif>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="lg:col-span-1 flex items-end">
                            <button type="submit" 
                                class="w-full h-9 flex items-center justify-center px-4 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            Filter
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Sales Table -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm table-fixed border border-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="w-[15%] px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-x border-gray-200">Invoice No</th>
                            <th class="w-[15%] px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-x border-gray-200">Date</th>
                            <th class="w-[35%] px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-x border-gray-200">Customer</th>
                            <th class="w-[15%] px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-x border-gray-200">Total</th>
                            <th class="w-[20%] px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-x border-gray-200">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($sales as $sale)
                        <tr class="hover:bg-gray-50">
                            <td class="px-2 py-2 whitespace-nowrap text-center border-x border-gray-200">
                                <div class="text-xs font-medium text-gray-900">#{{ $sale->sale_number }}</div>
                                <div class="text-xs text-gray-500 mt-0.5 flex justify-center">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] {{ $sale->payment_status=='Paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $sale->payment_status }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap text-center border-x border-gray-200">
                                <div class="text-xs text-gray-900">{{ date('M d, Y', strtotime($sale->sale_date)) }}</div>
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap text-center border-x border-gray-200">
                                <div class="text-xs text-gray-900">{{ $sale->customer->name ?? '-' }}</div>
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap text-center border-x border-gray-200">
                                <div class="text-xs font-medium text-gray-900">{{ number_format($sale->net_amount ?? $sale->total_amount ?? 0, 2) }}</div>
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap text-center border-x border-gray-200">
                                <div class="flex justify-center space-x-1">
                                    <a href="{{ route('sales.show', $sale->id) }}" 
                                       class="p-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition" title="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('sales.pdf', $sale->id) }}" 
                                       class="p-1 bg-gray-600 text-white rounded hover:bg-gray-700 transition"
                                       target="_blank" title="Print PDF">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this sale?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 bg-red-600 text-white rounded hover:bg-red-700 transition" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No sales found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">
                {{ $sales->links() }}
            </div>
        </div>
    </div>
@endsection
