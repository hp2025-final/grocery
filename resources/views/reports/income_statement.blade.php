@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto p-2 sm:p-4 md:p-6">
    <h1 class="text-xl md:text-2xl font-bold mb-4 md:mb-6">Income Statement</h1>
    <form method="GET" class="flex flex-wrap gap-2 md:gap-4 mb-4 md:mb-6 items-end">
        <div>
            <label class="block text-xs md:text-sm font-medium text-gray-700">From</label>
            <input type="date" name="from" value="{{ request('from') }}" class="mt-1 block w-full border border-gray-300 rounded px-2 py-1 md:px-3 md:py-2 text-xs md:text-sm">
        </div>
        <div>
            <label class="block text-xs md:text-sm font-medium text-gray-700">To</label>
            <input type="date" name="to" value="{{ request('to') }}" class="mt-1 block w-full border border-gray-300 rounded px-2 py-1 md:px-3 md:py-2 text-xs md:text-sm">
        </div>
        <button type="submit" class="bg-gray-900 text-white px-3 py-1 md:px-4 md:py-2 rounded text-xs md:text-sm">Filter</button>
    </form>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded shadow text-xs md:text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-2 py-1 md:px-4 md:py-2 text-left">Category</th>
                    <th class="px-2 py-1 md:px-4 md:py-2 text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="px-2 py-1 md:px-4 md:py-2">Total Income</td>
                    <td class="px-2 py-1 md:px-4 md:py-2 text-right">{{ number_format($totalIncome ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td class="px-2 py-1 md:px-4 md:py-2">Cost of Goods Sold (COGS)</td>
                    <td class="px-2 py-1 md:px-4 md:py-2 text-right">{{ number_format($cogs ?? 0, 2) }}</td>
                </tr>
                <tr class="font-bold border-t">
                    <td class="px-2 py-1 md:px-4 md:py-2">Gross Profit</td>
                    <td class="px-2 py-1 md:px-4 md:py-2 text-right">{{ number_format($grossProfit ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td class="px-2 py-1 md:px-4 md:py-2">Operating Expenses</td>
                    <td class="px-2 py-1 md:px-4 md:py-2 text-right">{{ number_format($operatingExpenses ?? 0, 2) }}</td>
                </tr>
                <tr class="font-bold border-t">
                    <td class="px-2 py-1 md:px-4 md:py-2">Operating Profit</td>
                    <td class="px-2 py-1 md:px-4 md:py-2 text-right">{{ number_format($operatingProfit ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
