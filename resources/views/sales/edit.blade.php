@extends('layouts.app')
@section('content')
<style>
    /* Base styles */
    .sales-form input,
    .sales-form select,
    .sales-form textarea,
    .sales-form label {
        font-size: 0.875rem;
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
        padding: 1rem;
        margin-bottom: 1rem;
    }

    /* Row layouts */
    .product-row {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
        align-items: flex-end;
    }

    .product-field {
        width: 80%;
    }

    .remove-button {
        width: 20%;
        display: flex;
        justify-content: flex-end;
    }

    .details-row {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 0.75rem;
    }

    .unit-field {
        width: 25%;
    }

    .quantity-field {
        width: 35%;
    }

    .price-field {
        width: 40%;
    }

    .total-row {
        width: 100%;
    }
</style>

<div class="max-w-4xl mx-auto">
    <div class="sales-form bg-white rounded-xl shadow p-4 md:p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Edit Sale</h1>
            <a href="{{ route('sales.create') }}" class="inline-block px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 transition">Back to Sales</a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded bg-green-100 border border-green-200 text-green-700 px-4 py-3 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        
        <form method="POST" action="{{ route('sales.update', $sale->id) }}" x-data="salesForm()" @submit.prevent="submitForm">
        @csrf
        @method('PUT')
            <div class="space-y-4">
                <!-- Date and Customer Selection -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Sale Date <span class="text-red-500">*</span></label>
                        <input type="date" name="sale_date" x-model="saleDate" value="{{ $sale->sale_date }}" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Customer <span class="text-red-500">*</span></label>
                        <select name="customer_id" x-model="customerId" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                            <option value="">Select Customer</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ $sale->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>
</div>

                <!-- Products Section -->
                <div class="space-y-4">
                    <template x-for="(item, idx) in items" :key="idx">
                        <div class="product-item">
                            <!-- Row 1: Product and Remove Button -->
                            <div class="product-row">
                                <div class="product-field">
                                    <label class="block text-xs font-medium mb-1">Product</label>
                                    <select :name="`products[${idx}][product_id]`"
                                        class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                        x-model="item.product_id" @change="updateUnit(idx)" required>
                                        <option value="">Select</option>
                                        <template x-for="product in products" :key="product.id">
                                            <option :value="product.id" x-text="product.name"></option>
                                        </template>
                    </select>
                                </div>
                                <div class="remove-button">
                                    <button type="button"
                                        class="text-red-500 hover:text-red-700 font-bold text-xl px-2 focus:outline-none"
                                        @click="removeItem(idx)">&times;</button>
                                </div>
                            </div>

                            <!-- Row 2: Unit, Quantity, Unit Price -->
                            <div class="details-row">
                                <div class="unit-field">
                                    <label class="block text-xs font-medium mb-1">Unit</label>
                                    <input type="text"
                                        class="block w-full rounded border border-gray-200 bg-gray-100 px-3 py-2 text-gray-700 focus:outline-none"
                                        :value="item.unit_name" readonly>
                                </div>
                                <div class="quantity-field">
                                    <label class="block text-xs font-medium mb-1">Quantity</label>
                                    <input type="number" min="0.01" step="0.01" :name="`products[${idx}][quantity]`"
                                        class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                        x-model.number="item.quantity" @input="updateTotal(idx)" required>
                                </div>
                                <div class="price-field">
                                    <label class="block text-xs font-medium mb-1">Unit Price</label>
                                    <input type="number" min="0" step="0.01" :name="`products[${idx}][unit_price]`"
                                        class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                        x-model.number="item.unit_price" @input="updateTotal(idx)" required>
                                </div>
                            </div>

                            <!-- Row 3: Total -->
                            <div class="total-row">
                                <label class="block text-xs font-medium mb-1">Total</label>
                                <input type="text"
                                    class="block w-full rounded border border-gray-200 bg-gray-100 px-3 py-2 text-right text-gray-700 focus:outline-none"
                                    :value="item.total.toFixed(2)" readonly>
                            </div>
                        </div>
                    </template>
                </div>

                <button type="button" 
                    class="w-full md:w-auto px-4 py-2 border border-blue-500 text-blue-500 rounded hover:bg-blue-50 transition-colors" 
                    @click="addItem">+ Add Product</button>

                <!-- Discount and Notes -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Discount</label>
                        <input type="number" min="0" step="0.01" name="discount" x-model.number="discount" value="{{ $sale->discount_amount }}"
                            class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Notes <span class="text-red-500">*</span></label>
                        <textarea name="notes" rows="2" x-model="notes"
                            class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>{{ $sale->notes }}</textarea>
                    </div>
</div>

                <!-- Grand Total -->
                <div class="flex justify-end">
                    <div class="bg-blue-50 rounded p-4 min-w-[220px] text-center">
                        <span class="font-semibold text-base">Grand Total: </span>
                        <span class="font-bold text-xl text-blue-800" x-text="(subtotal - discount).toFixed(2)"></span>
</div>
</div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-4">
                    <div class="space-x-2">
                        <a href="{{ route('sales.create') }}" class="inline-block px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 transition">Cancel</a>
                        <button type="submit" class="inline-block px-4 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700 transition">
                            Update Sale
                        </button>
    </div>
</div>
</div>
    </form>
</div>
</div>

<script>
function salesForm() {
    return {
        products: @json($products),
        items: {!! json_encode($sale->items->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->rate,
                'unit_name' => $item->unit->name ?? '',
                'total' => $item->total_amount
            ];
        })) !!},
        discount: {{ $sale->discount_amount ?? 0 }},
        notes: @json($sale->notes),
        saleDate: '{{ $sale->sale_date }}',
        customerId: '{{ $sale->customer_id }}',
        get subtotal() {
            return this.items.reduce((sum, i) => sum + (i.quantity * i.unit_price || 0), 0);
        },
        get netTotal() {
            return Math.max(0, this.subtotal - (this.discount || 0));
        },
        addItem() {
            this.items.push({ product_id: '', quantity: 1, unit_price: 0, unit_name: '', total: 0 });
        },
        removeItem(idx) {
            if(this.items.length > 1) this.items.splice(idx, 1);
        },
        updateUnit(idx) {
            let prod = this.products.find(p => p.id == this.items[idx].product_id);
            this.items[idx].unit_name = prod ? prod.unit_name : '';
            if (prod) {
                this.items[idx].unit_price = prod.sale_price;
                this.updateTotal(idx);
            }
        },
        updateTotal(idx) {
            let item = this.items[idx];
            item.total = (item.quantity || 0) * (item.unit_price || 0);
        },
        submitForm(e) {
            e.target.submit();
        }
    }
}
</script>
@endsection
