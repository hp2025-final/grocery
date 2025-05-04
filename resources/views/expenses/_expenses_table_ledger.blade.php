<table class="min-w-full text-sm">
    <thead>
        <tr class="bg-gray-100">
            <th class="px-4 py-2 text-left">Sl#</th>
            <th class="px-4 py-2 text-left">Date</th>
            <th class="px-4 py-2 text-left">Expense Account</th>
            <th class="px-4 py-2 text-left">Payment Account</th>
            <th class="px-4 py-2 text-left">Description</th>
            <th class="px-4 py-2 text-right">Amount (Rs.)</th>
<th class="px-4 py-2 text-right">Balance</th>
        </tr>
    </thead>
    <tbody>
        @php $balance = isset($openingBalance) ? $openingBalance : 0; @endphp
        @if(isset($openingBalance))
        <tr class="bg-yellow-50 font-semibold">
            <td class="px-4 py-2"></td>
            <td class="px-4 py-2"></td>
            <td class="px-4 py-2"></td>
            <td class="px-4 py-2"></td>
            <td class="px-4 py-2">Opening Balance</td>
            <td class="px-4 py-2 text-right"></td>
            <td class="px-4 py-2 text-right">{{ number_format($openingBalance, 2) }}</td>
        </tr>
        @endif
        @forelse($expenses as $expense)
        @php $balance += $expense->amount ?? 0; @endphp
        <tr class="@if($loop->even) bg-gray-50 @endif">
            <td class="px-4 py-2">{{ ($expenses->firstItem() ?? 0) + $loop->index }}</td>
            <td class="px-4 py-2">{{ $expense->date }}</td>
            <td class="px-4 py-2">{{ $expense->expenseAccount->name ?? '-' }}</td>
            <td class="px-4 py-2">{{ $expense->paymentAccount->name ?? '-' }}</td>
            <td class="px-4 py-2">{{ $expense->description }}</td>
            <td class="px-4 py-2 text-right">{{ number_format($expense->amount ?? 0, 2) }}</td>
            <td class="px-4 py-2 text-right">{{ number_format($balance, 2) }}</td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center py-4 text-gray-400">No expenses found.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="mt-4">{{ $expenses->links() }}</div>
