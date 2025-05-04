@if($payments->count())
<div class="overflow-x-auto">
    <table class="min-w-full bg-white rounded-xl shadow text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left font-bold tracking-wider">S#</th>
                <th class="px-4 py-2 text-left font-bold tracking-wider">Date</th>
                <th class="px-4 py-2 text-left font-bold tracking-wider">Vendor</th>
                <th class="px-4 py-2 text-left font-bold tracking-wider">Amount</th>
                <th class="px-4 py-2 text-left font-bold tracking-wider">Bank</th>
                <th class="px-4 py-2 text-left font-bold tracking-wider">Reference</th>
                <th class="px-4 py-2 text-left font-bold tracking-wider">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($payments as $payment)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 align-middle">{{ $loop->iteration + ($payments->currentPage() - 1) * $payments->perPage() }}</td>
                    <td class="px-4 py-2 align-middle">{{ $payment->payment_date }}</td>
                    <td class="px-4 py-2 align-middle">{{ optional($payment->vendor)->name }}</td>
                    <td class="px-4 py-2 align-middle">{{ number_format($payment->amount_paid, 2) }}</td>
                    <td class="px-4 py-2 align-middle">
    {{ optional(optional($payment->paymentAccount)->bank)->name ?? optional($payment->paymentAccount)->name }}
</td>
                    <td class="px-4 py-2 align-middle">{{ $payment->reference }}</td>
                    <td class="px-4 py-2 align-middle flex space-x-2">

                        <form action="{{ route('vendor-payments.destroy', $payment->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this vendor payment? This will also delete related journal entries and lines.');" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-block bg-red-100 text-red-700 rounded-full p-2 hover:bg-red-200" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">
        <div class="flex justify-center">
            @if($payments->hasPages())
                <nav class="inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    {{-- Previous Page Link --}}
                    @if ($payments->onFirstPage())
                        <span class="px-3 py-1 rounded-l-md bg-gray-200 text-gray-500 cursor-not-allowed">Prev</span>
                    @else
                        <a href="{{ $payments->previousPageUrl() }}" data-page="{{ $payments->currentPage() - 1 }}" class="px-3 py-1 rounded-l-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 cursor-pointer">Prev</a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($payments->getUrlRange(1, $payments->lastPage()) as $page => $url)
                        @if ($page == $payments->currentPage())
                            <span class="px-3 py-1 bg-blue-500 text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" data-page="{{ $page }}" class="px-3 py-1 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 cursor-pointer">{{ $page }}</a>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($payments->hasMorePages())
                        <a href="{{ $payments->nextPageUrl() }}" data-page="{{ $payments->currentPage() + 1 }}" class="px-3 py-1 rounded-r-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 cursor-pointer">Next</a>
                    @else
                        <span class="px-3 py-1 rounded-r-md bg-gray-200 text-gray-500 cursor-not-allowed">Next</span>
                    @endif
                </nav>
            @endif
        </div>
    </div>
</div>
@else
    <div class="p-4 text-gray-500">No vendor payments found.</div>
@endif
