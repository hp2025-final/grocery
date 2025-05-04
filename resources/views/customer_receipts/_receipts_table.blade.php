<div x-data="{ showModal: false, selectedReceipt: {} }" @keydown.escape.window="showModal = false" class="overflow-x-auto">
    <table class="min-w-full bg-white rounded-xl shadow text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left font-bold tracking-wider">S#</th>
                <th class="px-4 py-2 text-left font-bold tracking-wider">Date</th>
                <th class="px-4 py-2 text-left font-bold tracking-wider">Customer</th>
                <th class="px-4 py-2 text-left font-bold tracking-wider">Amount</th>
                <th class="px-4 py-2 text-left font-bold tracking-wider">Bank</th>
                <th class="px-4 py-2 text-left font-bold tracking-wider">Reference</th>
                <th class="px-4 py-2 text-left font-bold tracking-wider">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($receipts as $receipt)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 align-middle">{{ $loop->iteration + ($receipts->currentPage() - 1) * $receipts->perPage() }}</td>
                    <td class="px-4 py-2 align-middle">{{ $receipt->receipt_date }}</td>
                    <td class="px-4 py-2 align-middle">{{ $receipt->customer->name ?? '' }}</td>
                    <td class="px-4 py-2 align-middle">{{ number_format($receipt->amount_received, 2) }}</td>
                    <td class="px-4 py-2 align-middle">
    {{ optional(optional($receipt->paymentAccount)->bank)->name ?? optional($receipt->paymentAccount)->name }}
</td>
                    <td class="px-4 py-2 align-middle">{{ $receipt->reference }}</td>
                    <td class="px-4 py-2 align-middle">
    <div class="flex items-center justify-center gap-2">
        <!-- View Button -->
        <button @click="selectedReceipt = {
            receipt_number: '{{ $receipt->receipt_number }}',
            receipt_date: '{{ $receipt->receipt_date }}',
            customer_name: '{{ $receipt->customer->name ?? '' }}',
            amount_received: '{{ number_format($receipt->amount_received, 2) }}',
            bank: '{{ optional(optional($receipt->paymentAccount)->bank)->name ?? optional($receipt->paymentAccount)->name }}',
            reference: '{{ $receipt->reference }}',
            user_name: '{{ optional($receipt->user)->name ?? '' }}'
        }; showModal = true" type="button" class="w-7 h-7 p-0.5 flex items-center justify-center rounded-full bg-blue-500 hover:bg-blue-600 shadow transition text-xs" title="View">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm8 0c0 5-7 9-8 9s-8-4-8-9a8 8 0 1116 0z" /></svg>
        </button>
        <!-- Delete Button -->
        <form action="{{ route('customer-receipts.destroy', $receipt->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this receipt?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-7 h-7 p-0.5 flex items-center justify-center rounded-full bg-red-500 hover:bg-red-600 shadow transition text-xs" title="Delete">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </form>
    </div>
</td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-8 text-center text-gray-400">No receipts found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">
        <div class="flex justify-center">
            @if($receipts->hasPages())
                <nav class="inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    {{-- Previous Page Link --}}
                    @if ($receipts->onFirstPage())
                        <span class="px-3 py-1 rounded-l-md bg-gray-200 text-gray-500 cursor-not-allowed">Prev</span>
                    @else
                        <a href="{{ $receipts->previousPageUrl() }}" data-page="{{ $receipts->currentPage() - 1 }}" class="px-3 py-1 rounded-l-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 cursor-pointer">Prev</a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($receipts->getUrlRange(1, $receipts->lastPage()) as $page => $url)
                        @if ($page == $receipts->currentPage())
                            <span class="px-3 py-1 bg-blue-500 text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" data-page="{{ $page }}" class="px-3 py-1 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 cursor-pointer">{{ $page }}</a>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($receipts->hasMorePages())
                        <a href="{{ $receipts->nextPageUrl() }}" data-page="{{ $receipts->currentPage() + 1 }}" class="px-3 py-1 rounded-r-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 cursor-pointer">Next</a>
                    @else
                        <span class="px-3 py-1 rounded-r-md bg-gray-200 text-gray-500 cursor-not-allowed">Next</span>
                    @endif
                </nav>
            @endif
        </div>
    </div>

    <!-- Alpine.js Modal for Viewing Receipt Details -->
    <template x-if="showModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6 relative">
                <button @click="showModal = false" class="absolute top-2 right-2 w-7 h-7 flex items-center justify-center rounded-full bg-gray-200 hover:bg-gray-300 text-gray-700" title="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
                <h3 class="text-xl font-bold mb-4 text-center">Customer Receipt Details</h3>
                <div class="space-y-2">
                    <div><span class="font-semibold">Receipt Number:</span> <span x-text="selectedReceipt.receipt_number"></span></div>
                    <div><span class="font-semibold">Date:</span> <span x-text="selectedReceipt.receipt_date"></span></div>
                    <div><span class="font-semibold">Customer Name:</span> <span x-text="selectedReceipt.customer_name"></span></div>
                    <div><span class="font-semibold">Amount:</span> <span x-text="selectedReceipt.amount_received"></span></div>
                    <div><span class="font-semibold">Bank:</span> <span x-text="selectedReceipt.bank"></span></div>
                    <div><span class="font-semibold">Reference:</span> <span x-text="selectedReceipt.reference"></span></div>
                    <div><span class="font-semibold">User Name:</span> <span x-text="selectedReceipt.user_name"></span></div>
                </div>
            </div>
        </div>
    </template>
</div>
