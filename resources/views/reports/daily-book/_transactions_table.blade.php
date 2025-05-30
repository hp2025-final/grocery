@if($transactions->count())
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Party</th>
                <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                <th scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($transactions as $transaction)
            <tr class="hover:bg-gray-50">
                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                    {{ $transaction['date']->format('d-m-Y') }}
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                    {{ $transaction['number'] }}
                </td>
                <td class="px-3 py-2 text-sm text-gray-900">
                    {{ $transaction['description'] }}
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                    {{ $transaction['party'] }}
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 text-right">
                    {{ number_format($transaction['amount'], 2) }}
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-center">
                    @php
                        $model = $transaction['model'];
                        $viewRoute = match($transaction['type']) {
                            'Sale' => route('sales.show', $model->id),
                            'Purchase' => route('purchases.show', $model->id),
                            'Receipt' => route('customer-receipts.create', $model->id),
                            'Payment' => route('vendor-payments.show', $model->id),
                            'Expense' => route('expenses.edit', $model->id),
                            default => '#'
                        };
                    @endphp
                    @if($viewRoute !== '#')
                    <a href="{{ $viewRoute }}" class="inline-flex items-center p-1 bg-gray-100 hover:bg-gray-200 rounded transition-colors">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
