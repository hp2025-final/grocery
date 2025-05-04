@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto py-8">
    <h2 class="text-2xl font-bold mb-6">Inventory Ledger: {{ $inventory->name ?? $inventory->title ?? 'N/A' }} ({{ $inventory->inventory_code ?? $inventory->code ?? $inventory->id }})</h2>



    <form method="get" class="mb-6 flex flex-wrap gap-4 items-end">
        <div>
            <label for="from" class="block text-xs font-semibold text-gray-600 mb-1">From Date</label>
            <input type="date" name="from" id="from" class="rounded border-gray-300 px-2 py-1" value="{{ $from }}">
        </div>
        <div>
            <label for="to" class="block text-xs font-semibold text-gray-600 mb-1">To Date</label>
            <input type="date" name="to" id="to" class="rounded border-gray-300 px-2 py-1" value="{{ $to }}">
        </div>
        <div>
            <button type="submit" class="bg-blue-600 text-white rounded px-4 py-2 font-semibold hover:bg-blue-700">Filter</button>
        </div>
    </form>
    <div class="bg-white rounded-xl shadow overflow-x-auto mt-8">
        <table class="min-w-full text-sm rounded-xl">
            <thead class="bg-blue-50">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold">Date</th>
                    <th class="px-3 py-2 text-left font-semibold">Party</th>
                    <th class="px-3 py-2 text-right font-semibold">In</th>
                    <th class="px-3 py-2 text-right font-semibold">Out</th>
                    <th class="px-3 py-2 text-right font-semibold">Balance</th>
                    <th class="px-3 py-2 text-right font-semibold">Unit</th>
                    <th class="px-3 py-2 text-right font-semibold">Rate</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rows as $row)
                <tr>
                    <td class="px-3 py-2">{{ $row['date'] ?? '' }}</td>
                    <td class="px-3 py-2">{{ $row['party'] ?? '' }}</td>
                    <td class="px-3 py-2 text-right">{{ $row['in'] ?? '' }}</td>
                    <td class="px-3 py-2 text-right">{{ $row['out'] ?? '' }}</td>
                    <td class="px-3 py-2 text-right">{{ $row['balance'] ?? '' }}</td>
                    <td class="px-3 py-2 text-right">{{ $row['unit'] ?? '' }}</td>
                    <td class="px-3 py-2 text-right">{{ $row['rate'] ?? '' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-3 py-2 text-center text-gray-400">No data found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $rows->links() }}
        </div>
    </div>
</div>
@endsection
