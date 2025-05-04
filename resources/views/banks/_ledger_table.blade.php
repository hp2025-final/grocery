<table class="min-w-full text-sm rounded-xl">
    <thead class="bg-blue-50">
        <tr>
            <th class="px-4 py-3 font-bold">Date</th>
            <th class="px-4 py-3 font-bold">Type</th>
            <th class="px-4 py-3 font-bold">Accounts</th>
            <th class="px-4 py-3 font-bold">Reference</th>
            <th class="px-4 py-3 font-bold">Notes</th>
            <th class="px-4 py-3 font-bold text-right">Debit</th>
            <th class="px-4 py-3 font-bold text-right">Credit</th>
            <th class="px-4 py-3 font-bold text-right">Balance</th>
            <th class="px-4 py-3 font-bold">Created At</th>
        </tr>
    </thead>
    <tbody class="divide-y">
        @if(isset($openingBalance))
            <tr class="bg-yellow-50 font-semibold">
                <td class="px-4 py-2">{{ $from }}</td>
                <td class="px-4 py-2">Opening balance for bank: {{ $bank->name }}</td>
                <td class="px-4 py-2"></td>
                <td class="px-4 py-2"></td>
                <td class="px-4 py-2 text-right">0.00</td>
                <td class="px-4 py-2 text-right">0.00</td>
                <td class="px-4 py-2 text-right">{{ number_format($openingBalance, 2) }}</td>
            </tr>
        @endif
        @forelse($entries as $entry)
            <tr class="odd:bg-gray-50 even:bg-white">
                <td class="px-4 py-2">{{ $entry['date'] }}</td>
                <td class="px-4 py-2">{{ $entry['type'] }}</td>
                <td class="px-4 py-2">{{ $entry['description'] }}</td>
                <td class="px-4 py-2">{{ $entry['reference'] ?? '' }}</td>
                <td class="px-4 py-2">{{ $entry['notes'] ?? '' }}</td>
                <td class="px-4 py-2 text-right">{{ $entry['debit'] }}</td>
                <td class="px-4 py-2 text-right">{{ $entry['credit'] }}</td>
                <td class="px-4 py-2 text-right">{{ $entry['balance'] }}</td>
                <td class="px-4 py-2">{{ $entry['created_at'] ?? '' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center py-6 text-gray-500">No ledger entries found for this bank.</td>
            </tr>
        @endforelse
    </tbody>
</table>
<div class="mt-4">
    {{ $entries->links() }}
</div>
