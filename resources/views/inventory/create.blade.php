@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto py-8">
    <div class="flex flex-col md:flex-row gap-8" x-data="inventoryForm()">
        <div class="md:w-1/3 w-full">
            @if ($errors->any())
                <div class="mb-4 p-3 rounded bg-red-100 border border-red-400 text-red-800">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if(session('success'))
                <div class="mb-4 p-3 rounded bg-green-100 border border-green-400 text-green-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            <form method="POST" :action="editMode ? updateUrl : createUrl" class="bg-white p-6 rounded shadow">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>
<script>
function inventoryForm() {
    return {
        editMode: false,
        id: null,
        inventory_code: '',
        name: '',
        category_id: '',
        unit: '',
        buy_price: '',
        sale_price: '',
        opening_qty: '',
        notes: '',
        get total() { return (parseFloat(this.opening_qty || 0) * parseFloat(this.buy_price || 0)).toFixed(2); },
        createUrl: '{{ route('inventory.store') }}',
        updateUrl: '',
        editProduct(product) {
            this.editMode = true;
            this.id = product.id;
            this.inventory_code = product.inventory_code;
            this.name = product.name;
            this.category_id = product.category_id;
            this.unit = product.unit;
            this.buy_price = product.buy_price;
            this.sale_price = product.sale_price;
            this.opening_qty = product.opening_qty;
            this.notes = product.notes;

            this.updateUrl = `/inventory/${product.id}`;
        },
        resetForm() {
            this.editMode = false;
            this.id = null;
            this.inventory_code = '';
            this.name = '';
            this.category_id = '';
            this.unit = '';
            this.buy_price = '';
            this.sale_price = '';
            this.opening_qty = '';
            this.notes = '';

            this.updateUrl = '';
        },
    }
}
</script>
                @csrf
        <div class="mb-4">
            <label class="block font-semibold mb-1">Product Code</label>
            <input type="text" name="inventory_code" x-model="inventory_code" :value="inventory_code || '{{ old('inventory_code', $nextCode) }}'" readonly class="w-full border-gray-300 rounded px-3 py-2 bg-gray-100" />
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" x-model="name" required class="w-full border-gray-300 rounded px-3 py-2" />
            @error('name')
                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Category <span class="text-red-500">*</span></label>
            <select name="category_id" x-model="category_id" required class="w-full border-gray-300 rounded px-3 py-2">
                <option value="">Select Category</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            @error('category_id')
                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Unit <span class="text-red-500">*</span></label>
            <select name="unit" x-model="unit" required class="w-full border-gray-300 rounded px-3 py-2">
                <option value="">Select Unit</option>
                @foreach($units as $unit)
                    <option value="{{ $unit }}">{{ $unit }}</option>
                @endforeach
            </select>
            @error('unit')
                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4 flex gap-4">
            <div class="flex-1">
                <label class="block font-semibold mb-1">Buy Price <span class="text-red-500">*</span></label>
                <input type="number" name="buy_price" x-model="buy_price" min="0.01" step="0.01" required class="w-full border-gray-300 rounded px-3 py-2" />
                @error('buy_price')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="flex-1">
                <label class="block font-semibold mb-1">Sale Price <span class="text-red-500">*</span></label>
                <input type="number" name="sale_price" x-model="sale_price" min="0.01" step="0.01" required class="w-full border-gray-300 rounded px-3 py-2" />
                @error('sale_price')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="mb-4">
    <label class="block font-semibold mb-1">Opening Qty</label>
    <input type="number" name="opening_qty" x-model="opening_qty" min="0" step="0.01" class="w-full border-gray-300 rounded px-3 py-2" />
    @error('opening_qty')
        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
    @enderror
</div>

        <div class="mb-6">
            <span class="text-sm text-gray-600">Total Opening Value: <span class="font-bold" x-text="total"></span></span>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" x-text="editMode ? 'Update Product' : 'Create Product'"></button>
            <template x-if="editMode">
                <button type="button" @click="resetForm()" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
            </template>
        </div>
    </form>
        </div>
        <div class="md:w-2/3 w-full">
            <h2 class="text-xl font-semibold mb-4">All Products</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 shadow rounded-lg overflow-hidden">
                    <thead class="bg-blue-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">#</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Code</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Name</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Category</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Unit</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Buy Price</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Sale Price</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($allProducts as $i => $product)
                            <tr class="hover:bg-blue-50 transition">
                                <td class="px-3 py-2 text-center text-sm text-gray-800">{{ $i + 1 }}</td>
                                <td class="px-3 py-2 text-sm text-gray-800">{{ $product->inventory_code }}</td>
                                <td class="px-3 py-2 text-sm text-gray-800">{{ $product->name }}</td>
                                <td class="px-3 py-2 text-sm text-gray-800">{{ optional($product->category)->name }}</td>
                                <td class="px-3 py-2 text-sm text-gray-800">{{ $product->unit }}</td>
                                <td class="px-3 py-2 text-sm text-gray-800">{{ number_format($product->buy_price, 2) }}</td>
                                <td class="px-3 py-2 text-sm text-gray-800">{{ number_format($product->sale_price, 2) }}</td>
                                <td class="px-3 py-2 text-sm flex gap-2">
                                    <a href="/inventory/{{ $product->id }}/ledger" class="inline-flex items-center justify-center w-8 h-8 bg-blue-600 hover:bg-blue-700 text-white rounded-full transition" title="Ledger">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </a>
                                    <button type="button"
                                        class="inline-flex items-center justify-center w-8 h-8 bg-yellow-400 hover:bg-yellow-500 text-white rounded-full transition"
                                        title="Edit"
                                        @click="editProduct({
                                            id: {{ $product->id }},
                                            inventory_code: '{{ $product->inventory_code }}',
                                            name: `{{ addslashes($product->name) }}`,
                                            category_id: {{ $product->category_id }},
                                            unit: '{{ $product->unit }}',
                                            buy_price: {{ $product->buy_price }},
                                            sale_price: {{ $product->sale_price }},
                                            opening_qty: {{ $product->opening_qty ?? 0 }},
                                            notes: `{{ addslashes($product->notes ?? '') }}`
                                        })">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    
                                    <form action="{{ route('inventory.destroy', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 bg-red-500 hover:bg-red-600 text-white rounded-full transition" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m4-6v.01M5 7V5a2 2 0 012-2h10a2 2 0 012 2v2M5 7h14" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-4 text-gray-500">No products found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
