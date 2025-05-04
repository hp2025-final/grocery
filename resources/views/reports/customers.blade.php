@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Customer Report</h1>
    <form method="get" class="flex flex-wrap items-end gap-4 mb-6">
        <div>
            <label class="block text-sm font-medium">From</label>
            <input type="date" name="from" value="{{ $from }}" class="form-input" />
        </div>
        <div>
            <label class="block text-sm font-medium">To</label>
            <input type="date" name="to" value="{{ $to }}" class="form-input" />
        </div>
        <div>
            <label class="block text-sm font-medium">Customer</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search name..." class="form-input" />
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
    <div class="overflow-x-auto">
        <table class="table-auto w-full border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-2 py-1 border">#</th>
                    <th class="px-2 py-1 border text-left">Customer</th>
<th class="px-2 py-1 border text-center">Ledger</th>
                    <th class="px-2 py-1 border text-right">Opening Balance</th>
                    <th class="px-2 py-1 border text-right">Debit</th>
                    <th class="px-2 py-1 border text-right">Credit</th>
                    <th class="px-2 py-1 border text-right">Closing Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $i => $row)
                    <tr>
                        <td class="border px-2 py-1 text-center">{{ $i+1 }}</td>
                        <td class="border px-2 py-1">{{ $row['customer']->name }}</td>
<td class="border px-2 py-1 text-center">
    <a href="{{ route('customers.ledger', ['id' => $row['customer']->id]) }}" class="inline-block px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-full text-xs font-semibold transition" title="Ledger">
        <svg xmlns="http://www.w3.org/2000/svg" class="inline w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4h9" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
        Ledger
    </a>
</td>
                        <td class="border px-2 py-1 text-right">{{ number_format($row['opening'], 2) }}</td>
                        <td class="border px-2 py-1 text-right">{{ number_format($row['debit'], 2) }}</td>
                        <td class="border px-2 py-1 text-right">{{ number_format($row['credit'], 2) }}</td>
                        <td class="border px-2 py-1 text-right font-semibold">{{ number_format($row['closing'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4">No customers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
