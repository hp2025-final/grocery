<table class="min-w-full text-sm">
    <thead>
        <tr class="bg-gray-100">
            
            @php
    $currentSort = request('sort', 'created_at');
    $currentDir = request('direction', 'desc');
    function sort_link($label, $col, $currentSort, $currentDir) {
        $arrow = '';
        if ($currentSort === $col) {
            $arrow = $currentDir === 'asc' ? ' ▲' : ' ▼';
        }
        return '<button type="button" x-on:click=\'setSort("' . $col . '")\' class="hover:underline focus:outline-none">' . $label . $arrow . '</button>';
    }
@endphp
<th class="px-4 py-2 text-left">{!! sort_link('Created At', 'created_at', $currentSort, $currentDir) !!}</th>
<th class="px-4 py-2 text-left">{!! sort_link('Date', 'date', $currentSort, $currentDir) !!}</th>
<th class="px-4 py-2 text-left">{!! sort_link('Expense Account', 'expense_account_id', $currentSort, $currentDir) !!}</th>
<th class="px-4 py-2 text-right">{!! sort_link('Amount (Rs.)', 'amount', $currentSort, $currentDir) !!}</th>
<th class="px-4 py-2 text-left">{!! sort_link('Payment Account', 'payment_account_id', $currentSort, $currentDir) !!}</th>
<th class="px-4 py-2 text-left">{!! sort_link('Description', 'description', $currentSort, $currentDir) !!}</th>
<th class="px-4 py-2 text-center">Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($expenses as $expense)
        <tr class="@if($loop->even) bg-gray-50 @endif">
            
            <td class="px-4 py-2">{{ $expense->created_at->format('Y-m-d H:i') }}</td>
            <td class="px-4 py-2">{{ $expense->date }}</td>
            <td class="px-4 py-2">{{ $expense->expenseAccount->name ?? '-' }}</td>
            <td class="px-4 py-2 text-right">{{ number_format($expense->amount ?? 0, 2) }}</td>
            <td class="px-4 py-2">{{ $expense->paymentAccount->name ?? '-' }}</td>
            <td class="px-4 py-2">{{ $expense->description }}</td>
            <td class="px-4 py-2 text-center">
                
                <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-800 mx-1" title="Delete" onclick="return confirm('Are you sure you want to delete this expense?')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center py-4 text-gray-400">No expenses found.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="mt-4">{{ $expenses->links() }}</div>
