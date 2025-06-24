@extends('layouts.app')
@section('content')
<style>
    /* Base styles */
    .sales-form input,
    .sales-form select,
    .sales-form textarea {
        font-size: 0.75rem;
        padding-top: 0.35rem;
        padding-bottom: 0.35rem;
        height: 2rem;
    }
    
    .sales-form label {
        font-size: 0.7rem;
        margin-bottom: 0.25rem;
    }

    /* Table styles */
    .invoice-table {
        font-size: 12px;
    }

    .invoice-table th {
        font-weight: 600;
        background-color: #f3f4f6;
    }

    .invoice-table .invoice-header {
        font-size: 12px;
    }

    .invoice-table .invoice-header .label {
        font-weight: 600;
        color: #4b5563;
    }

    .invoice-table .invoice-header .value {
        font-weight: 500;
    }

    .invoice-table .amount {
        font-weight: 600;
    }    
    
    /* Product item layout */
    .product-item {
        background-color: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.5rem;
        margin-bottom: 0.5rem;
    }    

    /* Single row layout for products */
    .product-row {
        display: flex;
        gap: 0.25rem;
        align-items: flex-end;
        width: 100%;
    }

    .category-field {
        width: 25%;
    }

    .product-field {
        width: 35%;
    }

    .unit-field {
        width: 15%;
    }

    .quantity-field {
        width: 18%;
    }

    .price-field {
        width: 18%;
    }

    .total-field {
        width: 19%;
    }
    
    .remove-button {
        width: 10%;
        display: flex;
        justify-content: flex-end;
    }
</style>

<div class="max-w-7xl mx-auto">
    <div class="sales-form bg-white rounded-xl shadow p-2 md:p-4 w-full overflow-hidden">
        <h1 class="text-lg font-bold mb-3 text-gray-800">Edit Sale</h1>
        
        @if($errors->any())
            <div class="mb-4 rounded bg-red-100 border border-red-200 text-red-700 px-4 py-3">
                <strong class="font-bold">Error!</strong>
                <ul class="mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        @if(session('success'))
            <div class="mb-4 rounded bg-green-100 border border-green-200 text-green-700 px-4 py-3 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        
        @if(session('error'))
            <div class="mb-4 rounded bg-red-100 border border-red-200 text-red-700 px-4 py-3">
                {{ session('error') }}
            </div>
        @endif
        
        <form method="POST" action="{{ route('sales.update', $sale->id) }}" x-data="salesForm()" @submit="submitForm">
            @csrf
            @method('PUT')
            <div class="space-y-2">
                <!-- Date and Customer Selection -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-medium mb-0.5">Sale Date <span class="text-red-500">*</span></label>
                        <input type="date" name="sale_date" x-model="saleDate" 
                               class="block w-full rounded border border-gray-300 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-400" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-0.5">Customer <span class="text-red-500">*</span></label>
                        <select name="customer_id" x-model="customerId" 
                                class="block w-full rounded border border-gray-300 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-400" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Products Section -->
                <div class="space-y-4">
                    <template x-for="(item, idx) in items" :key="idx">
                        <div class="product-item">
                            <div class="product-row">
                                <div class="category-field">
                                    <label class="block text-xs font-medium mb-0.5">Category</label>
                                    <select x-model="item.category_id" @change="updateProductOptions(idx)"
                                            class="block w-full rounded border border-gray-300 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-400">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="product-field">
                                    <label class="block text-xs font-medium mb-0.5">Product</label>
                                    <select :name="`products[${idx}][product_id]`"
                                            class="block w-full rounded border border-gray-300 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-400"
                                            x-model="item.product_id" @change="updateUnit(idx)" required>
                                        <option value="">Select</option>
                                        <template x-for="product in filteredProducts(idx)" :key="product.id">
                                            <option :value="String(product.id)" 
                                                    :selected="String(product.id) === String(item.product_id)"
                                                    x-text="product.name"></option>
                                        </template>
                                    </select>
                                </div>

                                <div class="unit-field">
                                    <label class="block text-xs font-medium mb-0.5">Unit</label>
                                    <input type="text"
                                           class="block w-full rounded border border-gray-200 bg-gray-100 px-2 text-xs text-gray-700 focus:outline-none"
                                           :value="item.unit_name" readonly>
                                </div>
                                
                                <div class="quantity-field">
                                    <label class="block text-xs font-medium mb-0.5">Qty</label>
                                    <input type="number" min="0.01" step="0.01" :name="`products[${idx}][quantity]`"
                                           class="block w-full rounded border border-gray-300 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-400"
                                           x-model.number="item.quantity" @input="updateTotal(idx)" required>
                                </div>
                                
                                <div class="price-field">
                                    <label class="block text-xs font-medium mb-0.5">Price</label>
                                    <input type="number" min="0" step="0.01" :name="`products[${idx}][unit_price]`"
                                           class="block w-full rounded border border-gray-300 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-400"
                                           x-model.number="item.unit_price" @input="updateTotal(idx)" required>
                                </div>
                                
                                <div class="total-field">
                                    <label class="block text-xs font-medium mb-0.5">Total</label>
                                    <input type="text"
                                           class="block w-full rounded border border-gray-200 bg-gray-100 px-2 text-xs text-right text-gray-700 focus:outline-none"
                                           :value="item.total.toFixed(2)" readonly>
                                </div>
                                
                                <div class="remove-button">
                                    <label class="block text-xs font-medium mb-0.5">&nbsp;</label>
                                    <button type="button"
                                            class="block w-full rounded text-red-500 hover:text-red-700 hover:bg-red-50 border border-red-200 px-1 py-1 text-xs font-bold text-center focus:outline-none"
                                            @click="removeItem(idx)">&times;</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <button type="button" 
                        class="w-full md:w-auto px-2 py-1 text-xs border border-blue-500 text-blue-500 rounded hover:bg-blue-50 transition-colors" 
                        @click="addItem">+ Add Product</button>

                <!-- Discount and Notes -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-medium mb-0.5">Discount</label>
                        <input type="number" min="0" step="0.01" name="discount" x-model.number="discount"
                               class="block w-full rounded border border-gray-300 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-400" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium mb-0.5">Notes <span class="text-red-500">*</span></label>
                        <textarea name="notes" rows="1" x-model="notes"
                                  class="block w-full rounded border border-gray-300 px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-400" required></textarea>
                    </div>
                </div>

                <!-- Grand Total -->
                <div class="flex justify-end">
                    <div class="bg-blue-50 rounded px-4 py-2 min-w-[200px] text-right">
                        <span class="text-sm font-medium text-gray-600">Grand Total:</span>
                        <span class="ml-2 text-lg font-bold text-blue-600" x-text="grandTotal.toFixed(2)"></span>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-2 pt-4">
                    <a href="{{ route('sales.index') }}" class="px-3 py-1.5 border border-gray-300 rounded text-gray-600 text-sm hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 transition-colors">
                        Update Sale
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function salesForm() {
    return {
        categories: @json($categories),
        products: @json($products),
        items: [
            @foreach($sale->items as $item)
            {
                category_id: '{{ $item->product->category_id ?? '' }}',
                product_id: '{{ $item->product_id }}',
                quantity: {{ $item->quantity }},
                unit_price: {{ $item->rate }},
                unit_name: '{{ $item->unit->name ?? '' }}',
                total: {{ $item->total_amount }},
                isInitialLoad: true // Track if this is initial data
            }@if(!$loop->last),@endif
            @endforeach
        ],
        discount: {{ $sale->discount_amount ?? 0 }},
        notes: @json($sale->notes ?? ''),
        saleDate: '{{ $sale->sale_date }}',
        customerId: '{{ $sale->customer_id }}',
        
        init() {
            // Ensure proper data types for dropdowns
            this.items.forEach((item, idx) => {
                // Convert to string to match option values
                item.category_id = String(item.category_id);
                item.product_id = String(item.product_id);
            });
            
            // After initialization, mark all items as no longer initial load
            setTimeout(() => {
                this.items.forEach(item => {
                    item.isInitialLoad = false;
                });
            }, 100);
        },
        
        get subtotal() {
            return this.items.reduce((sum, item) => sum + (item.total || 0), 0);
        },
        get grandTotal() {
            return this.subtotal - (this.discount || 0);
        },
        addItem() {
            this.items.push({
                category_id: '',
                product_id: '',
                quantity: 1,
                unit_price: 0,
                unit_name: '',
                total: 0,
                isInitialLoad: false
            });
        },
        removeItem(idx) {
            if(this.items.length > 1) {
                this.items.splice(idx, 1);
            }
        },
        filteredProducts(idx) {
            const categoryId = this.items[idx].category_id;
            const currentProductId = this.items[idx].product_id;
            const isInitialLoad = this.items[idx].isInitialLoad;
            
            // If no category is selected, show all products
            if (!categoryId) {
                return this.products.map(product => ({
                    ...product,
                    id: String(product.id),
                    category_id: String(product.category_id)
                }));
            }
            
            // Filter by category
            let filtered = this.products.filter(p => String(p.category_id) === String(categoryId));
            
            // During initial load, include the currently selected product even if from different category
            // This ensures that existing sale items show their selected products during edit
            if (isInitialLoad && currentProductId) {
                const currentProduct = this.products.find(p => String(p.id) === String(currentProductId));
                if (currentProduct && !filtered.some(p => String(p.id) === String(currentProductId))) {
                    filtered.unshift(currentProduct);
                }
            }
            
            // Ensure all product IDs are strings for consistent comparison
            return filtered.map(product => ({
                ...product,
                id: String(product.id),
                category_id: String(product.category_id)
            }));
        },
        updateProductOptions(idx) {
            // When category changes, reset the product selection and related fields
            // Also mark this item as no longer in initial load state
            this.items[idx].product_id = '';
            this.items[idx].unit_name = '';
            this.items[idx].unit_price = 0;
            this.items[idx].isInitialLoad = false;
            this.updateTotal(idx);
        },
        updateUnit(idx) {
            const prod = this.products.find(p => String(p.id) === String(this.items[idx].product_id));
            this.items[idx].unit_name = prod ? prod.unit_name : '';
            if (prod && prod.sale_price != null) {
                this.items[idx].unit_price = prod.sale_price;
            }
            this.updateTotal(idx);
        },
        updateTotal(idx) {
            const item = this.items[idx];
            item.total = (item.quantity || 0) * (item.unit_price || 0);
        },
        submitForm(e) {
            // Validate that we have at least one item with product selected
            if (this.items.length === 0 || !this.items.some(item => item.product_id)) {
                alert('Please add at least one product to the sale.');
                e.preventDefault();
                return false;
            }
            
            // Set form fields to match Alpine.js data before submitting
            document.querySelector('input[name="sale_date"]').value = this.saleDate;
            document.querySelector('select[name="customer_id"]').value = this.customerId;
            document.querySelector('input[name="discount"]').value = this.discount;
            document.querySelector('textarea[name="notes"]').value = this.notes;
            
            // Let the form submit normally
        }
    }
}
</script>
@endsection
