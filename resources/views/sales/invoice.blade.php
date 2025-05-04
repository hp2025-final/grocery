@extends('layouts.app')
@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow p-8 mt-8">
    <h1 class="text-2xl font-bold mb-4">Sale Invoice</h1>
    <div class="mb-4">
        <div class="flex justify-between">
            <div>
                <div class="font-semibold">Invoice No:</div>
                <div>{{ $sale->sale_number }}</div>
            </div>
            <div>
                <div class="font-semibold">Date:</div>
                <div>{{ $sale->sale_date }}</div>
            </div>
        </div>
        <div class="mt-4">
            <div class="font-semibold">Customer Name:</div>
            <div>{{ $sale->customer->name ?? '-' }}</div>
        </div>
    </div>
    <div class="overflow-x-auto mb-4">
        <table class="min-w-full text-sm border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 border">#</th>
                    <th class="px-4 py-2 border">Product</th>
                    <th class="px-4 py-2 border">Quantity</th>
                    <th class="px-4 py-2 border">Unit</th>
                    <th class="px-4 py-2 border">Unit Price</th>
                    <th class="px-4 py-2 border">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $i => $item)
                <tr>
                    <td class="px-4 py-2 border">{{ $i+1 }}</td>
                    <td class="px-4 py-2 border">{{ $item->product->name ?? '-' }}</td>
                    <td class="px-4 py-2 border text-right">{{ number_format($item->quantity, 2) }}</td>
                    <td class="px-4 py-2 border">{{ $item->unit->name ?? '-' }}</td>
                    <td class="px-4 py-2 border text-right">{{ number_format($item->rate, 2) }}</td>
                    <td class="px-4 py-2 border text-right">{{ number_format($item->total_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="flex justify-end mt-4">
        <div class="w-full md:w-1/2">
            <div class="flex justify-between mb-2">
                <span class="font-semibold">Subtotal:</span>
                <span>{{ number_format($sale->total_amount, 2) }}</span>
            </div>
            <div class="flex justify-between mb-2">
                <span class="font-semibold">Discount:</span>
                <span>{{ number_format($sale->discount_amount, 2) }}</span>
            </div>
            <div class="flex justify-between text-lg font-bold border-t pt-2">
                <span>Grand Total:</span>
                <span>{{ number_format($sale->net_amount, 2) }}</span>
            </div>
        </div>
    </div>
    <div class="mt-8 text-center text-gray-500 text-xs">Thank you for your business!</div>
</div>
@endsection
