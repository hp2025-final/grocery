@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto py-8 px-4" x-data="inventoryReport()">
    <div class="mb-6 flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Inventory by Category Report</h1>
        <div class="flex flex-col sm:flex-row gap-4 items-stretch sm:items-center w-full sm:w-auto">
            <form action="{{ route('reports.inventory.by-category') }}" method="GET" class="flex gap-2 w-full sm:w-auto">
                <select name="category" class="rounded border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500">
                    <option value="">All Categories</option>
                    @foreach($allCategories as $category)
                        <option value="{{ $category->id }}" {{ $selectedCategory == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm">
                    Filter
                </button>
                @if($selectedCategory)
                    <a href="{{ route('reports.inventory.by-category') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded hover:bg-gray-200 text-sm inline-flex items-center">
                        Reset
                    </a>
                @endif
            </form>
            <button onclick="window.print()" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm inline-flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print Report
            </button>
        </div>    </div>    <div class="rounded-lg shadow overflow-hidden bg-white">
        @foreach($categories as $category)
        <div class="border-b border-gray-200 last:border-b-0">
            <!-- Category Header -->
            <div class="px-4 py-3 cursor-pointer hover:bg-gray-50" 
                 @click="toggleCategory('{{ $category->id }}')">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform transition-transform" 
                             :class="{'rotate-90': openCategories.includes('{{ $category->id }}'), 'rotate-0': !openCategories.includes('{{ $category->id }}')}"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $category->name }}</h3>
                        <span class="text-sm text-gray-600">({{ $category->total_products }} products)</span>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-600">Total Value:</div>
                        <div class="font-semibold">{{ number_format($category->total_value, 2) }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Category Products -->
            <div x-show="openCategories.includes('{{ $category->id }}')" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0">
                <table class="min-w-full divide-y divide-gray-200">                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Unit</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700">Sale Price (Rs.)</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700">Opening</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700">In</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700">Out</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700">Available</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700">Value (Rs.)</th>
                        </tr>
                    </thead>                    <tbody class="divide-y divide-gray-200">
                        @foreach($category->inventories as $product)
                        @php
                            $available_qty = ($product->opening_qty ?? 0) + ($product->total_in ?? 0) - ($product->total_out ?? 0);
                        @endphp                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-sm">{{ $product->name }}</td>
                            <td class="px-4 py-2 text-sm">{{ $product->unit }}</td>
                            <td class="px-4 py-2 text-sm text-right">{{ number_format($product->sale_price, 2) }}</td>
                            <td class="px-4 py-2 text-sm text-right">{{ number_format($product->opening_qty ?? 0, 2) }}</td>
                            <td class="px-4 py-2 text-sm text-right">{{ number_format($product->total_in ?? 0, 2) }}</td>
                            <td class="px-4 py-2 text-sm text-right">{{ number_format($product->total_out ?? 0, 2) }}</td>
                            <td class="px-4 py-2 text-sm text-right">{{ number_format($available_qty, 2) }}</td>
                            <td class="px-4 py-2 text-sm text-right">
                                {{ number_format($available_qty * $product->sale_price, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                <tfoot>
                        <tr>
                            <td colspan="8" class="px-4 py-2 text-sm font-semibold text-right border-t border-gray-200">Category Total:</td>
                            <td class="px-4 py-2 text-sm font-semibold text-right border-t border-gray-200">
                                {{ number_format($category->total_value, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endforeach    </div>    <!-- Grand Total -->    <div class="mt-4 px-6 py-4 rounded-lg bg-white shadow">
        <div class="flex justify-between items-center">
            <div>
                <div class="text-lg font-semibold text-gray-900">Total Categories: {{ $categories->count() }}</div>
                <div class="text-lg font-semibold text-gray-900">Total Products: {{ $categories->sum('total_products') }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-600">Grand Total Value:</div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format($categories->sum('total_value'), 2) }}</div>
            </div>
        </div>
    </div>
</div>

<script>
function inventoryReport() {
    return {
        openCategories: [],
        init() {
            // Initialize with all categories open by default
            @foreach($categories as $category)
                this.openCategories.push('{{ $category->id }}');
            @endforeach
        },
        toggleCategory(id) {
            // Click to hide functionality
            const index = this.openCategories.indexOf(id);
            if (index === -1) {
                // Show if hidden
                this.openCategories.push(id);
            } else {
                // Hide if shown
                this.openCategories.splice(index, 1); 
            }
        }
    }
}
</script>

<style>
@media print {
    .bg-white { background-color: white !important; }
    .bg-gray-50 { background-color: #f9fafb !important; }
    .shadow { box-shadow: none !important; }
    button { display: none !important; }
    thead { background-color: #f9fafb !important; }
}
</style>
@endsection
