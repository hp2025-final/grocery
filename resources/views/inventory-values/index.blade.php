@extends('layouts.app')

@section('title', 'Inventory Values')

@section('content')
<div class="max-w-6xl mx-auto px-2 py-4 sm:py-6">
    <div class="bg-white rounded-lg shadow">
        <div class="p-4">
            <h2 class="text-lg font-semibold mb-4">Inventory Values</h2>

            <form action="{{ route('inventory-values.index') }}" method="GET" class="grid grid-cols-2 sm:grid-cols-5 gap-2 mb-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">From:</label>
                    <input type="date" name="from" value="{{ $from }}" 
                           class="w-full border-gray-300 rounded text-sm px-2 py-1">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">To:</label>
                    <input type="date" name="to" value="{{ $to }}" 
                           class="w-full border-gray-300 rounded text-sm px-2 py-1">
                </div>
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full bg-blue-600 text-white px-3 py-1.5 rounded text-sm hover:bg-blue-700">
                        Filter
                    </button>
                </div>
                <div class="flex items-end">
                    <a href="{{ route('inventory-values.export-pdf', ['from' => $from, 'to' => $to]) }}" 
                       class="w-full bg-green-500 text-white px-3 py-1.5 rounded text-sm text-center hover:bg-green-600">
                        Export PDF
                    </a>
                </div>
                <div class="flex items-end">
                    <a href="{{ route('inventory-values.info') }}" 
                       class="w-full bg-yellow-500 text-white px-3 py-1.5 rounded text-sm text-center hover:bg-yellow-600">
                        <i class="fas fa-info-circle mr-1"></i> Info
                    </a>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs sm:text-[12px] font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-3 py-2 text-right text-xs sm:text-[12px] font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                            <th class="px-3 py-2 text-right text-xs sm:text-[12px] font-medium text-gray-500 uppercase tracking-wider">Avg. Buy Price</th>
                            <th class="px-3 py-2 text-right text-xs sm:text-[12px] font-medium text-gray-500 uppercase tracking-wider">Avg. Sale Price</th>
                            <th class="px-3 py-2 text-right text-xs sm:text-[12px] font-medium text-gray-500 uppercase tracking-wider">Opening Value</th>
                            <th class="px-3 py-2 text-right text-xs sm:text-[12px] font-medium text-gray-500 uppercase tracking-wider">IN Value</th>
                            <th class="px-3 py-2 text-right text-xs sm:text-[12px] font-medium text-gray-500 uppercase tracking-wider">OUT Value</th>
                            <th class="px-3 py-2 text-right text-xs sm:text-[12px] font-medium text-gray-500 uppercase tracking-wider">Closing Value</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($inventory as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-sm sm:text-[12px] text-gray-900">{{ $item->name }}</td>
                                <td class="px-3 py-2 text-sm sm:text-[12px] text-gray-900 text-right">{{ $item->unit }}</td>
                                <td class="px-3 py-2 text-sm sm:text-[12px] text-gray-900 text-right">{{ $item->unit_price }}</td>
                                <td class="px-3 py-2 text-sm sm:text-[12px] text-gray-900 text-right">{{ $item->sale_price }}</td>
                                <td class="px-3 py-2 text-sm sm:text-[12px] text-gray-900 text-right">{{ $item->opening_value }}</td>
                                <td class="px-3 py-2 text-sm sm:text-[12px] text-gray-900 text-right">{{ $item->period_in_value }}</td>
                                <td class="px-3 py-2 text-sm sm:text-[12px] text-gray-900 text-right">{{ $item->period_out_value }}</td>
                                <td class="px-3 py-2 text-sm sm:text-[12px] text-gray-900 text-right">{{ $item->closing_value }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-3 py-2 text-sm text-center text-gray-500">No records found</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="2" class="px-3 py-2 text-sm sm:text-[12px] font-medium text-gray-900">Total</td>
                            <td class="px-3 py-2 text-sm sm:text-[12px] font-medium text-gray-900 text-right">-</td>
                            <td class="px-3 py-2 text-sm sm:text-[12px] font-medium text-gray-900 text-right">-</td>
                            <td class="px-3 py-2 text-sm sm:text-[12px] font-medium text-gray-900 text-right">{{ number_format($inventory->sum(function($item) { return (float)str_replace(',', '', $item->opening_value); }), 2) }}</td>
                            <td class="px-3 py-2 text-sm sm:text-[12px] font-medium text-gray-900 text-right">{{ number_format($inventory->sum(function($item) { return (float)str_replace(',', '', $item->period_in_value); }), 2) }}</td>
                            <td class="px-3 py-2 text-sm sm:text-[12px] font-medium text-gray-900 text-right">{{ number_format($inventory->sum(function($item) { return (float)str_replace(',', '', $item->period_out_value); }), 2) }}</td>
                            <td class="px-3 py-2 text-sm sm:text-[12px] font-medium text-gray-900 text-right">{{ number_format($inventory->sum(function($item) { return (float)str_replace(',', '', $item->closing_value); }), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function applyFilter() {
        const from = document.getElementById('from').value;
        const to = document.getElementById('to').value;
        window.location.href = `{{ route('inventory-values.index') }}?from=${from}&to=${to}`;
    }
</script>
@endpush 