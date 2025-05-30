@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Product List by Category</h1>
        <a href="{{ route('reports.inventory.product-list.export') }}" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 text-sm inline-flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export PDF
        </a>
    </div>

    @php
        $totalProducts = 0;
    @endphp
    
    @foreach($categories as $category)
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="bg-gray-100 px-4 py-3 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">{{ $category->name }}</h2>
                <p class="text-sm text-gray-600">{{ $category->inventories->count() }} products</p>
            </div>
            
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Product Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700">Unit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($category->inventories as $index => $product)
                        @php
                            $totalProducts++;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">{{ $product->name }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">{{ $product->unit }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-sm text-gray-500 text-center">No products in this category</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="mt-4 text-sm text-gray-600">
        <div>Total Categories: {{ $categories->count() }}</div>
        <div>Total Products: {{ $totalProducts }}</div>
    </div>
</div>

<style>
@media print {
    .bg-white { background-color: white !important; }
    .bg-gray-50 { background-color: #f9fafb !important; }
    .shadow { box-shadow: none !important; }
    button { display: none !important; }
}
</style>
@endsection
