@if(isset($entries) && $entries->count())
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0">
                <tr>
                    <th scope="col" class="px-2 md:px-4 py-2 text-left text-xs md:text-sm font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Date</th>
                    <th scope="col" class="px-2 md:px-4 py-2 text-left text-xs md:text-sm font-medium text-gray-500 uppercase tracking-wider">From</th>
                    <th scope="col" class="px-2 md:px-4 py-2 text-left text-xs md:text-sm font-medium text-gray-500 uppercase tracking-wider">To</th>
                    <th scope="col" class="px-2 md:px-4 py-2 text-left text-xs md:text-sm font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th scope="col" class="px-2 md:px-4 py-2 text-left text-xs md:text-sm font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th scope="col" class="px-2 md:px-4 py-2 text-right text-xs md:text-sm font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($entries as $transfer)
                    <tr class="hover:bg-gray-50">
                        <td class="px-2 md:px-4 py-2 whitespace-nowrap text-xs md:text-sm text-gray-900">{{ \Carbon\Carbon::parse($transfer->date)->format('d M Y') }}</td>
                        <td class="px-2 md:px-4 py-2 text-xs md:text-sm text-gray-900 break-words">
                            {{ $transfer->fromBank->name }}
                            <div class="text-gray-500 text-xs">{{ $transfer->fromBank->account_number }}</div>
                        </td>
                        <td class="px-2 md:px-4 py-2 text-xs md:text-sm text-gray-900 break-words">
                            {{ $transfer->toBank->name }}
                            <div class="text-gray-500 text-xs">{{ $transfer->toBank->account_number }}</div>
                        </td>
                        <td class="px-2 md:px-4 py-2 whitespace-nowrap text-xs md:text-sm text-gray-900">
                            {{ number_format($transfer->amount, 2) }}
                        </td>
                        <td class="px-2 md:px-4 py-2 text-xs md:text-sm text-gray-900 break-words max-w-[100px] md:max-w-none truncate md:whitespace-normal">
                            {{ $transfer->description }}
                        </td>
                        <td class="px-2 md:px-4 py-2 whitespace-nowrap text-right text-xs md:text-sm font-medium">
                            <div class="flex justify-end space-x-1">
                                <form action="{{ route('bank_transfers.destroy', $transfer->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this transfer?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 p-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($entries->hasPages())
        <div class="px-2 md:px-4 py-3 flex items-center justify-between border-t border-gray-200">
            <div class="text-xs md:text-sm text-gray-700">
                Showing {{ $entries->firstItem() }} to {{ $entries->lastItem() }} of {{ $entries->total() }} results
            </div>
            <div class="flex space-x-1">
                @if ($entries->onFirstPage())
                    <span class="px-2 py-1 text-xs md:text-sm rounded border text-gray-400">Previous</span>
                @else
                    <a href="#" data-page="{{ $entries->currentPage() - 1 }}" class="px-2 py-1 text-xs md:text-sm rounded border text-blue-600 hover:bg-blue-50">Previous</a>
                @endif

                @if ($entries->hasMorePages())
                    <a href="#" data-page="{{ $entries->currentPage() + 1 }}" class="px-2 py-1 text-xs md:text-sm rounded border text-blue-600 hover:bg-blue-50">Next</a>
                @else
                    <span class="px-2 py-1 text-xs md:text-sm rounded border text-gray-400">Next</span>
                @endif
            </div>
        </div>
    @endif
@else
    <div class="text-gray-400 text-center py-12">
        No transfers found.
    </div>
@endif
