@extends('layouts.app')
@section('content')
<div class="min-h-screen bg-gray-100 py-6">    <div class="max-w-5xl mx-auto">
        <!-- Invoice Card -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Company Header -->
            <div class="p-8 bg-gradient-to-r from-gray-50 to-white border-b">
                <div class="flex justify-between items-start">
                    <!-- Company Info -->
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-800">{{ config('company.name') }}</h1>
                        <div class="mt-2 text-sm text-gray-600 space-y-1">
                            <p class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                {{ config('company.phone') }}
                            </p>
                            <p class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                {{ config('company.email') }}
                            </p>
                        </div>
                    </div>                    <!-- Invoice Title -->
                    <div class="text-right">
                        <div class="text-right mb-4">
                            <h2 class="text-xs font-semibold text-gray-700">PURCHASE INVOICE</h2>
                            <p class="text-xs font-semibold text-gray-800 mt-1">#{{ $purchase->purchase_number }}</p>
                        </div>                        <div class="flex space-x-2">
                            <a href="{{ route('purchases.index') }}" 
                                class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium rounded transition-colors duration-150">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Back
                            </a>
                            <a href="{{ route('purchases.pdf', $purchase->id) }}"
                                class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium rounded transition-colors duration-150"
                                target="_blank">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Details -->
            <div class="p-8">
                <!-- Vendor & Invoice Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Vendor Information</h3>
                        <div class="text-sm text-gray-600 space-y-2">
                            <p class="font-medium text-gray-800">{{ $purchase->vendor->name }}</p>
                            @if($purchase->vendor->address)
                                <p class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $purchase->vendor->address }}
                                </p>
                            @endif
                            @if($purchase->vendor->phone)
                                <p class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    {{ $purchase->vendor->phone }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Invoice Details</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                            <div>
                                <p class="font-medium text-gray-500">Date Issued:</p>
                                <p class="text-gray-800">{{ date('d M Y', strtotime($purchase->purchase_date)) }}</p>
                            </div>
                            <div>
                                <p class="font-medium text-gray-500">Status:</p>
                                <p>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $purchase->payment_status=='Paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $purchase->payment_status }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Products Table -->
                <div class="mb-8 overflow-x-auto">
                    <table class="min-w-full border rounded-lg overflow-hidden">
                        <thead>
                            <tr class="bg-gray-50 border-b">
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($purchase->items as $index => $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item->product->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($item->quantity, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ $item->unit->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($item->rate, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($item->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr class="border-t">
                                <td colspan="4" class="px-4 py-3"></td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-500">Subtotal:</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($purchase->total_amount, 2) }}</td>
                            </tr>
                            @if($purchase->discount_amount > 0)
                            <tr>
                                <td colspan="4" class="px-4 py-3"></td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-500">Discount:</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($purchase->discount_amount, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="bg-gray-100">
                                <td colspan="4" class="px-4 py-3"></td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-700">Grand Total:</td>
                                <td class="px-4 py-3 text-sm font-bold text-gray-900 text-right">{{ number_format($purchase->net_amount, 2) }} Rs.</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Notes -->
                @if($purchase->notes)
                <div class="mb-8">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Notes</h3>
                    <p class="text-sm text-gray-600 p-4 bg-gray-50 rounded-lg border">{{ $purchase->notes }}</p>
                </div>
                @endif
                <!-- Footer -->
                <div class="text-center text-sm text-gray-500 mt-12 pt-8 border-t">
                    <p class="mb-2">{{ config('company.address') }}</p>
                    <p class="text-xs text-gray-400">{{ config('company.powered_by') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
