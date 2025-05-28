@extends('layouts.app')
@section('content')
<div class="max-w-5xl mx-auto py-8 px-2">
    <div class="flex justify-between items-end mb-6">
        <div>
            <h1 class="text-2xl font-bold mb-1 text-left">{{ $inventory->name ?? $inventory->title ?? 'N/A' }} ({{ $inventory->inventory_code ?? $inventory->code ?? $inventory->id }})</h1>
        </div>
        <div class="text-xl font-semibold text-gray-700 text-right">Inventory Ledger w/o Rate</div>
    </div>
    <hr class="mb-4 border-gray-300">

    <div class="flex justify-between items-end mb-4 w-full gap-2">
        <form method="get" class="flex flex-1 gap-2 items-end w-full">
            <div class="flex-1 min-w-0">
                <label for="from" class="block text-xs font-semibold mb-1">From</label>
                <input type="date" name="from" id="from" class="form-input form-input-sm rounded border-gray-300 w-full text-xs py-1.5 px-2 h-8" value="{{ $from }}">
            </div>
            <div class="flex-1 min-w-0">
                <label for="to" class="block text-xs font-semibold mb-1">To</label>
                <input type="date" name="to" id="to" class="form-input form-input-sm rounded border-gray-300 w-full text-xs py-1.5 px-2 h-8" value="{{ $to }}">
            </div>
            <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded hover:bg-blue-700 transition h-8 text-xs">Filter</button>
        </form>
        <a href="{{ route('inventory.ledger.without_rate.export', ['id' => $inventory->id, 'from' => $from, 'to' => $to]) }}"
           class="px-3 py-1.5 bg-green-600 text-white rounded hover:bg-green-700 transition flex items-center gap-2 h-8 text-xs whitespace-nowrap">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export PDF
        </a>
    </div>
    <hr class="mb-4 border-gray-300">
    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="min-w-full text-xs rounded-xl border-collapse border border-gray-200">
            <thead class="bg-blue-50">
                <tr>
                    <th class="px-3 py-2 font-bold w-[10%] text-center border border-gray-200">Date</th>
                    <th class="px-3 py-2 font-bold w-[50%] border border-gray-200">Party</th>
                    <th class="px-3 py-2 font-bold w-[15%] text-center border border-gray-200">In</th>
                    <th class="px-3 py-2 font-bold w-[15%] text-center border border-gray-200">Out</th>
                    <th class="px-3 py-2 font-bold w-[20%] text-center border border-gray-200">Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                <tr class="odd:bg-gray-50 even:bg-white">
                    <td class="px-3 py-1.5 text-center border border-gray-200">{{ isset($row['date']) ? \Carbon\Carbon::parse($row['date'])->format('d-m-Y') : '' }}</td>
                    <td class="px-3 py-1.5 border border-gray-200">{{ $row['party'] ?? '' }}</td>
                    <td class="px-3 py-1.5 text-center border border-gray-200">{{ isset($row['in']) && $row['in'] !== null ? number_format($row['in'], 0) : '' }}</td>
                    <td class="px-3 py-1.5 text-center border border-gray-200">{{ isset($row['out']) && $row['out'] !== null ? number_format($row['out'], 0) : '' }}</td>
                    <td class="px-3 py-1.5 text-center border border-gray-200">{{ isset($row['balance']) ? number_format($row['balance'], 0) : '' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4 text-gray-500 border border-gray-200">No data found for this inventory and date range.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">
            {{ $rows->links() }}
        </div>
    </div>
</div>
@endsection
