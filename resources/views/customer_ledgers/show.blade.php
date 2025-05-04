@extends('layouts.app')
@section('content')
<div class="max-w-5xl mx-auto py-8 px-2">
    <h1 class="text-2xl font-bold mb-6">Customer Ledger: {{ $customer->name }}</h1>
    
    <div class="flex justify-between items-end mb-4">
        <form method="get" class="flex flex-wrap gap-2 items-end">
            <div>
                <label class="block text-xs font-semibold mb-1">From</label>
                <input type="date" name="from" value="{{ $from }}" class="form-input rounded border-gray-300" />
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">To</label>
                <input type="date" name="to" value="{{ $to }}" class="form-input rounded border-gray-300" />
            </div>
            <button type="submit" class="ml-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Filter</button>
        </form>
        
        <a href="{{ route('customers.ledger.export', ['id' => $customer->id, 'from' => $from, 'to' => $to]) }}" 
           class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export PDF
        </a>
    </div>

    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="min-w-full text-sm rounded-xl">
            <thead class="bg-blue-50">
                <tr>
                    <th class="px-4 py-3 font-bold">Date</th>
                    <th class="px-4 py-3 font-bold">Type</th>
                    <th class="px-4 py-3 font-bold">Description</th>
                    <th class="px-4 py-3 font-bold">Notes</th>
                    <th class="px-4 py-3 font-bold text-right">Debit</th>
                    <th class="px-4 py-3 font-bold text-right">Credit</th>
                    <th class="px-4 py-3 font-bold text-right">Balance</th>
                    <th class="px-4 py-3 font-bold">Created At</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($rows as $row)
                    <tr class="odd:bg-gray-50 even:bg-white">
                        <td class="px-4 py-2">{{ $row['date'] }}</td>
                        <td class="px-4 py-2">{{ $row['type'] }}</td>
                        <td class="px-4 py-2 align-top">
                            @if (!empty($row['sale_items']))
                                <div class="text-xs text-gray-700 mt-1 space-y-0.5">
                                    @foreach($row['sale_items'] as $item)
                                        <div>
                                            {{ $item['product'] }}
                                            @if($item['qty'] !== '') ({{ $item['qty'] }} {{ $item['unit'] }}) @endif
                                            @if($item['rate'] !== '') x {{ is_numeric($item['rate']) ? number_format($item['rate'],2) : $item['rate'] }} @endif
                                            = {{ is_numeric($item['total']) ? number_format($item['total'],2) : $item['total'] }}
                                        </div>
                                    @endforeach
                                </div>
                            @elseif (!empty($row['receipt_bank']) || !empty($row['receipt_account_title']) || !empty($row['receipt_reference']))
                                <div class="text-xs text-blue-700 mt-1 space-y-0.5">
                                    @if(!empty($row['receipt_bank']))
                                        <div>Bank: {{ $row['receipt_bank'] }}</div>
                                    @endif
                                    @if(!empty($row['receipt_account_title']))
                                        <div>Account Title: {{ $row['receipt_account_title'] }}</div>
                                    @endif
                                    @if(!empty($row['receipt_reference']))
                                        <div>Reference: {{ $row['receipt_reference'] }}</div>
                                    @endif
                                </div>
                            @else
                                {{-- Only show description if not a generic label --}}
                                @php
                                    $generic = ['Accounts Receivable', 'Customer payment', 'Opening balance'];
                                @endphp
                                @if($row['description'] && !in_array($row['description'], $generic))
                                    {{ $row['description'] }}
                                @endif
                            @endif
                        </td>
                        <td class="px-4 py-2">{{ $row['notes'] ?? '' }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($row['debit'], 2) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($row['credit'], 2) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($row['balance'], 2) }}</td>
                        <td class="px-4 py-2">{{ $row['created_at'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center py-4 text-gray-500">No entries found for this customer and date range.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

