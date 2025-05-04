@extends('layouts.app')
@section('content')
<div class="max-w-5xl mx-auto p-2 sm:p-4 md:p-6">
    <h1 class="text-xl md:text-2xl font-bold mb-4 md:mb-6">General Ledger</h1>
    <form method="GET" class="flex flex-wrap gap-2 md:gap-4 mb-4 md:mb-6 items-end">
        <div>
            <label class="block text-xs md:text-sm font-medium text-gray-700">Account</label>
            <select name="account" class="mt-1 block w-full border border-gray-300 rounded px-2 py-1 md:px-3 md:py-2 text-xs md:text-sm">
                <option value="">-- Select Account --</option>
                @if(isset($accounts))
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" @if(isset($selectedAccountId) && $selectedAccountId == $account->id) selected @endif>{{ $account->name }}</option>
                    @endforeach
                @endif
            </select>
        </div>
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
                    <th class="px-2 py-1 md:px-4 md:py-2 text-left text-xs md:text-sm">Date</th>
                    <th class="px-2 py-1 md:px-4 md:py-2 text-left text-xs md:text-sm">Journal #</th>
                    <th class="px-2 py-1 md:px-4 md:py-2 text-left text-xs md:text-sm">Description</th>
                    <th class="px-2 py-1 md:px-4 md:py-2 text-right text-xs md:text-sm">Debit</th>
                    <th class="px-2 py-1 md:px-4 md:py-2 text-right text-xs md:text-sm">Credit</th>
                    <th class="px-2 py-1 md:px-4 md:py-2 text-right text-xs md:text-sm">Running Balance</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($ledger) && $ledger)
                    <tr>
                        <td class="px-2 py-1 md:px-4 md:py-2 text-xs md:text-sm">{{ $from ?? '...' }}</td>
                        <td class="px-2 py-1 md:px-4 md:py-2 text-xs md:text-sm"></td>
                        <td class="px-2 py-1 md:px-4 md:py-2 text-xs md:text-sm font-semibold">Opening Balance</td>
                        <td class="px-2 py-1 md:px-4 md:py-2 text-right text-xs md:text-sm">{{ number_format($ledger['opening_balance'], 2) }}</td>
                        <td class="px-2 py-1 md:px-4 md:py-2 text-right text-xs md:text-sm">0.00</td>
                        <td class="px-2 py-1 md:px-4 md:py-2 text-right text-xs md:text-sm">{{ number_format($ledger['opening_balance'], 2) }}</td>
                    </tr>
                    @foreach($ledger['transactions'] as $row)
                        <tr>
                            <td class="px-2 py-1 md:px-4 md:py-2 text-xs md:text-sm">{{ $row['date'] }}</td>
                            <td class="px-2 py-1 md:px-4 md:py-2 text-xs md:text-sm">{{ $row['journal_number'] }}</td>
                            <td class="px-2 py-1 md:px-4 md:py-2 text-xs md:text-sm">{{ $row['description'] }}</td>
                            <td class="px-2 py-1 md:px-4 md:py-2 text-right text-xs md:text-sm">{{ number_format($row['debit'], 2) }}</td>
                            <td class="px-2 py-1 md:px-4 md:py-2 text-right text-xs md:text-sm">{{ number_format($row['credit'], 2) }}</td>
                            <td class="px-2 py-1 md:px-4 md:py-2 text-right text-xs md:text-sm">{{ number_format($row['balance'], 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="px-2 py-1 md:px-4 md:py-2 text-center text-xs md:text-sm text-gray-400">No data available. Please select an account and filter.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
