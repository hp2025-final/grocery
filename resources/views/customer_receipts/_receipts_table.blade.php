<div class="overflow-x-auto">
    <table class="min-w-full bg-white text-xs divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-x border-gray-200 w-[15%]">Date</th>
                <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-x border-gray-200 w-[30%]">Customer</th>
                <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-x border-gray-200 w-[15%]">Amount</th>
                <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-x border-gray-200 w-[20%]">Bank</th>
                <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-x border-gray-200 w-[20%]">Notes</th>
                <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-x border-gray-200">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($receipts as $receipt)
                <tr class="hover:bg-gray-50">
                    <td class="px-2 py-2 whitespace-nowrap text-center border-x border-gray-200">
                        <div class="text-xs text-gray-900">{{ date('d-m-Y', strtotime($receipt->receipt_date)) }}</div>
                    </td>
                    <td class="px-2 py-2 whitespace-nowrap text-center border-x border-gray-200">
                        <div class="text-xs text-gray-900">{{ $receipt->customer->name ?? '-' }}</div>
                    </td>
                    <td class="px-2 py-2 whitespace-nowrap text-center border-x border-gray-200">
                        <div class="text-xs font-medium text-gray-900">{{ number_format($receipt->amount_received, 2) }}</div>
                    </td>
                    <td class="px-2 py-2 whitespace-nowrap text-center border-x border-gray-200">
                        <div class="text-xs text-gray-900">{{ optional(optional($receipt->paymentAccount)->bank)->name ?? optional($receipt->paymentAccount)->name }}</div>
                    </td>
                    <td class="px-2 py-2 whitespace-nowrap text-center border-x border-gray-200">
                        <div class="text-xs text-gray-900">{{ $receipt->notes }}</div>
                    </td>
                    <td class="px-2 py-2 whitespace-nowrap text-center border-x border-gray-200">
                        <div class="flex justify-center items-center space-x-1">
                            <!-- PDF Button -->
                            <a href="{{ route('customer-receipts.export-pdf', $receipt->id) }}" 
                               class="px-2 py-1 bg-gray-900 text-white text-xs rounded hover:bg-gray-800 transition inline-flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                PDF
                            </a>

                            <!-- Delete Button -->
                            <form action="{{ route('customer-receipts.destroy', $receipt->id) }}" 
                                method="POST" 
                                class="inline-block" 
                                onsubmit="return confirm('Are you sure you want to delete this receipt?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                    class="px-2 py-1 bg-gray-900 text-white text-xs rounded hover:bg-gray-800 transition">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-2 py-4 text-center text-gray-500">No receipts found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-4">
        <div class="flex justify-center">
            @if($receipts->hasPages())
                <nav class="relative z-0 inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    {{-- Previous Page Link --}}
                    @if ($receipts->onFirstPage())
                        <span class="relative inline-flex items-center px-2 py-1 text-xs font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md">Prev</span>
                    @else
                        <a href="{{ $receipts->previousPageUrl() }}" data-page="{{ $receipts->currentPage() - 1 }}" class="relative inline-flex items-center px-2 py-1 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">Prev</a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($receipts->getUrlRange(1, $receipts->lastPage()) as $page => $url)
                        @if ($page == $receipts->currentPage())
                            <span class="relative inline-flex items-center px-3 py-1 text-xs font-medium text-white bg-blue-600 border border-blue-600">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" data-page="{{ $page }}" class="relative inline-flex items-center px-3 py-1 text-xs font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">{{ $page }}</a>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($receipts->hasMorePages())
                        <a href="{{ $receipts->nextPageUrl() }}" data-page="{{ $receipts->currentPage() + 1 }}" class="relative inline-flex items-center px-2 py-1 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">Next</a>
                    @else
                        <span class="relative inline-flex items-center px-2 py-1 text-xs font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-r-md">Next</span>
                    @endif
                </nav>
            @endif
        </div>
    </div>
</div>
