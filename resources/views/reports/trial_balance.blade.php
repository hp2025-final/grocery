@extends('layouts.app')
@section('content')
<div class="max-w-5xl mx-auto p-2 sm:p-4 md:p-6">
    <h1 class="text-xl md:text-2xl font-bold mb-4 md:mb-6">Trial Balance</h1>
    <form method="GET" class="flex flex-wrap gap-2 md:gap-4 mb-4 md:mb-6 items-end bg-white p-4 rounded-lg shadow">
        <div>
            <label class="block text-xs md:text-sm font-medium text-gray-700">From Date</label>
            <input type="date" name="from_date" value="{{ request('from_date') }}" class="mt-1 block w-full border border-gray-300 rounded px-2 py-1 md:px-3 md:py-2 text-xs md:text-sm">
        </div>
        <div>
            <label class="block text-xs md:text-sm font-medium text-gray-700">To Date</label>
            <input type="date" name="to_date" value="{{ request('to_date') }}" class="mt-1 block w-full border border-gray-300 rounded px-2 py-1 md:px-3 md:py-2 text-xs md:text-sm">
        </div>
        <div>
            <label class="block text-xs md:text-sm font-medium text-gray-700">Account Type</label>
            <select name="account_type" class="form-select w-full border border-gray-300 rounded px-2 py-1 md:px-3 md:py-2 text-xs md:text-sm">
                <option value="">All</option>
                <option value="Asset" @if(request('account_type')=='Asset') selected @endif>Asset</option>
                <option value="Liability" @if(request('account_type')=='Liability') selected @endif>Liability</option>
                <option value="Equity" @if(request('account_type')=='Equity') selected @endif>Equity</option>
                <option value="Income" @if(request('account_type')=='Income') selected @endif>Income</option>
                <option value="Expense" @if(request('account_type')=='Expense') selected @endif>Expense</option>
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="bg-gray-900 text-white px-3 py-1 md:px-4 md:py-2 rounded text-xs md:text-sm w-full">View Report</button>
        </div>
    </form>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded shadow text-xs md:text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-2 py-1 md:px-4 md:py-2 text-left">Account Code</th>
                    <th class="px-2 py-1 md:px-4 md:py-2 text-left">Account Name</th>
                    <th class="px-2 py-1 md:px-4 md:py-2 text-right">Opening Debit</th>
                    <th class="px-2 py-1 md:px-4 md:py-2 text-right">Opening Credit</th>
                    <th class="px-2 py-1 md:px-4 md:py-2 text-right">Total Debit</th>
                    <th class="px-2 py-1 md:px-4 md:py-2 text-right">Total Credit</th>
                    <th class="px-2 py-1 md:px-4 md:py-2 text-right">Closing Debit</th>
                    <th class="px-2 py-1 md:px-4 md:py-2 text-right">Closing Credit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                <tr>
                    <td class="px-2 py-1 md:px-4 md:py-2">{{ $row['account_code'] }}</td>
                    <td class="px-2 py-1 md:px-4 md:py-2">{{ $row['account_name'] }}</td>
                    {{-- Opening Debit/Credit --}}
                    <td class="px-2 py-1 md:px-4 md:py-2 text-right">
                        @if(isset($row['opening_balance']))
                            @if(($row['type']=='Asset'||$row['type']=='Expense') ? $row['opening_balance'] >= 0 : $row['opening_balance'] < 0)
                                {{ number_format(abs($row['opening_balance']), 2) }}
                            @endif
                        @endif
                    </td>
                    <td class="px-2 py-1 md:px-4 md:py-2 text-right">
                        @if(isset($row['opening_balance']))
                            @if(($row['type']=='Asset'||$row['type']=='Expense') ? $row['opening_balance'] < 0 : $row['opening_balance'] >= 0)
                                {{ number_format(abs($row['opening_balance']), 2) }}
                            @endif
                        @endif
                    </td>
                    {{-- Period Debits/Credits --}}
                    <td class="px-2 py-1 md:px-4 md:py-2 text-right">{{ number_format($row['total_debit'] ?? 0, 2) }}</td>
                    <td class="px-2 py-1 md:px-4 md:py-2 text-right">{{ number_format($row['total_credit'] ?? 0, 2) }}</td>
                    {{-- Closing Debit/Credit --}}
                    <td class="px-2 py-1 md:px-4 md:py-2 text-right font-semibold">
                        @if(isset($row['closing_balance']))
                            @if(($row['type']=='Asset'||$row['type']=='Expense') ? $row['closing_balance'] >= 0 : $row['closing_balance'] < 0)
                                {{ number_format(abs($row['closing_balance']), 2) }}
                            @endif
                        @endif
                    </td>
                    <td class="px-2 py-1 md:px-4 md:py-2 text-right font-semibold">
                        @if(isset($row['closing_balance']))
                            @if(($row['type']=='Asset'||$row['type']=='Expense') ? $row['closing_balance'] < 0 : $row['closing_balance'] >= 0)
                                {{ number_format(abs($row['closing_balance']), 2) }}
                            @endif
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4 text-gray-400">No data found for selected filters.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="font-bold bg-gray-100">
                    <td colspan="6" class="px-2 py-1 md:px-4 md:py-2 text-right">Trial Balance Total</td>
                    <td class="px-2 py-1 md:px-4 md:py-2 text-right">
                        {{ number_format(
                            collect($rows)->reduce(function($carry, $row) {
                                return $carry + ((($row['type']=='Asset'||$row['type']=='Expense') ? ($row['closing_balance'] ?? 0) : -(($row['closing_balance'] ?? 0))) >= 0 ? abs($row['closing_balance'] ?? 0) : 0);
                            }, 0), 2) }}
                    </td>
                    <td class="px-2 py-1 md:px-4 md:py-2 text-right">
                        {{ number_format(
                            collect($rows)->reduce(function($carry, $row) {
                                return $carry + ((($row['type']=='Asset'||$row['type']=='Expense') ? ($row['closing_balance'] ?? 0) : -(($row['closing_balance'] ?? 0))) < 0 ? abs($row['closing_balance'] ?? 0) : 0);
                            }, 0), 2) }}
                    </td>
                </tr>
                @php
                    $closingDebit = collect($rows)->reduce(function($carry, $row) {
                        return $carry + ((($row['type']=='Asset'||$row['type']=='Expense') ? ($row['closing_balance'] ?? 0) : -(($row['closing_balance'] ?? 0))) >= 0 ? abs($row['closing_balance'] ?? 0) : 0);
                    }, 0);
                    $closingCredit = collect($rows)->reduce(function($carry, $row) {
                        return $carry + ((($row['type']=='Asset'||$row['type']=='Expense') ? ($row['closing_balance'] ?? 0) : -(($row['closing_balance'] ?? 0))) < 0 ? abs($row['closing_balance'] ?? 0) : 0);
                    }, 0);
                @endphp
                @if(abs($closingDebit - $closingCredit) > 0.01)
                <tr class="font-bold bg-red-100">
                    <td colspan="6" class="px-2 py-1 md:px-4 md:py-2 text-right text-red-600">Difference</td>
                    <td colspan="2" class="px-2 py-1 md:px-4 md:py-2 text-right text-red-600">
                        {{ number_format(abs($closingDebit - $closingCredit), 2) }}
                    </td>
                </tr>
                @endif
            </tfoot>
        </table>
    </div>
</div>
@endsection
