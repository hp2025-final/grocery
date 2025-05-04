@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto p-2 sm:p-4 md:p-6">
    <h1 class="text-xl md:text-2xl font-bold mb-4 md:mb-6">Balance Sheet</h1>
    <form method="GET" class="flex flex-wrap gap-2 md:gap-4 mb-4 md:mb-6 items-end">
        <div>
            <label class="block text-xs md:text-sm font-medium text-gray-700">As of</label>
            <input type="date" name="as_of" value="{{ request('as_of') }}" class="mt-1 block w-full border border-gray-300 rounded px-2 py-1 md:px-3 md:py-2 text-xs md:text-sm">
        </div>
        <button type="submit" class="bg-gray-900 text-white px-3 py-1 md:px-4 md:py-2 rounded text-xs md:text-sm">Filter</button>
    </form>
    <div class="overflow-x-auto">
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-2">Assets</h2>
            <table class="min-w-full bg-white rounded shadow text-xs md:text-sm mb-2">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-2 py-1 md:px-4 md:py-2 text-left">Account</th>
                        <th class="px-2 py-1 md:px-4 md:py-2 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assets as $row)
                        <tr>
                            <td class="px-2 py-1 md:px-4 md:py-2">{{ $row['name'] }}</td>
                            <td class="px-2 py-1 md:px-4 md:py-2 text-right">{{ number_format($row['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="font-bold border-t">
                        <td class="px-2 py-1 md:px-4 md:py-2">Total Assets</td>
                        <td class="px-2 py-1 md:px-4 md:py-2 text-right">{{ number_format($totalAssets, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-2">Liabilities</h2>
            <table class="min-w-full bg-white rounded shadow text-xs md:text-sm mb-2">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-2 py-1 md:px-4 md:py-2 text-left">Account</th>
                        <th class="px-2 py-1 md:px-4 md:py-2 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($liabilities as $row)
                        <tr>
                            <td class="px-2 py-1 md:px-4 md:py-2">{{ $row['name'] }}</td>
                            <td class="px-2 py-1 md:px-4 md:py-2 text-right">{{ number_format($row['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="font-bold border-t">
                        <td class="px-2 py-1 md:px-4 md:py-2">Total Liabilities</td>
                        <td class="px-2 py-1 md:px-4 md:py-2 text-right">{{ number_format($totalLiabilities, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-2">Equity</h2>
            <table class="min-w-full bg-white rounded shadow text-xs md:text-sm mb-2">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-2 py-1 md:px-4 md:py-2 text-left">Account</th>
                        <th class="px-2 py-1 md:px-4 md:py-2 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($equity as $row)
                        <tr>
                            <td class="px-2 py-1 md:px-4 md:py-2">{{ $row['name'] }}</td>
                            <td class="px-2 py-1 md:px-4 md:py-2 text-right">{{ number_format($row['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="font-bold border-t">
                        <td class="px-2 py-1 md:px-4 md:py-2">Total Equity</td>
                        <td class="px-2 py-1 md:px-4 md:py-2 text-right">{{ number_format($totalEquity, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-2">Final Balancing</h2>
            <table class="min-w-full bg-white rounded shadow text-xs md:text-sm">
                <tbody>
                    <tr>
                        <td class="px-2 py-1 md:px-4 md:py-2 font-bold">Total Assets</td>
                        <td class="px-2 py-1 md:px-4 md:py-2 text-right font-bold">{{ number_format($finalAssets, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="px-2 py-1 md:px-4 md:py-2 font-bold">Total Liabilities + Equity</td>
                        <td class="px-2 py-1 md:px-4 md:py-2 text-right font-bold">{{ number_format($finalLiabilitiesEquity, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
