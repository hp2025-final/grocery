@extends('layouts.app')
@section('content')
<div class="max-w-5xl mx-auto p-2 sm:p-4 md:p-6">
    <h1 class="text-xl md:text-2xl font-bold mb-4 md:mb-6">Journal Report</h1>
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
    <!-- Desktop Table View -->
    <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full bg-white rounded shadow text-xs md:text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-2 py-2 text-left">Date</th>
                    <th class="px-2 py-2 text-left">Description</th>
                    <th class="px-2 py-2 text-left">Source Type</th>
                    <th class="px-2 py-2 text-left">Lines</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-2 py-2">{{ $entry->date }}</td>
                    <td class="px-2 py-2">{{ $entry->description }}</td>
                    <td class="px-2 py-2">{{ ucfirst($entry->reference_type ?? 'Manual') }}</td>
                    <td class="px-2 py-2">
                        <div class="space-y-1">
                        @foreach($entry->lines as $line)
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold">{{ $line->account->name ?? '' }}</span>
                                <span class="inline-block bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs">D {{ number_format($line->debit ?? 0, 2) }}</span>
                                <span class="inline-block bg-red-100 text-red-700 px-2 py-0.5 rounded text-xs">C {{ number_format($line->credit ?? 0, 2) }}</span>
                            </div>
                        @endforeach
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-gray-500 py-4">No journal entries found for the selected period.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <!-- Mobile Card View -->
    <div class="block md:hidden space-y-4">
        @forelse($entries as $entry)
        <div class="bg-white rounded shadow p-3 border">
            <div class="flex justify-between items-center mb-1">
                <span class="font-mono text-xs bg-gray-100 px-2 py-0.5 rounded">{{ $entry->entry_number ?? $entry->id }}</span>
                <span class="text-xs text-gray-500">{{ $entry->date }}</span>
            </div>
            <div class="text-sm font-semibold mb-1">{{ $entry->description }}</div>
            <div class="text-xs mb-2 text-gray-600">Source: {{ ucfirst($entry->reference_type ?? 'Manual') }}</div>
            <div class="space-y-1">
                @foreach($entry->lines as $line)
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-semibold">{{ $line->account->name ?? '' }}</span>
                    <span class="inline-block bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs">D {{ number_format($line->debit ?? 0, 2) }}</span>
                    <span class="inline-block bg-red-100 text-red-700 px-2 py-0.5 rounded text-xs">C {{ number_format($line->credit ?? 0, 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="text-center text-gray-500 py-4">No journal entries found for the selected period.</div>
        @endforelse
    </div>
    <div class="mt-4">
        {{ $entries->appends(request()->except('page'))->links() }}
    </div>
</div>
@endsection
