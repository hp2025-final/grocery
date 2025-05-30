@extends('layouts.app')
@section('content')
<style>
    /* Base styles */
    .purchase-form input,
    .purchase-form select,
    .purchase-form textarea {
        font-size: 0.75rem;
        padding-top: 0.35rem;
        padding-bottom: 0.35rem;
        height: 2rem;
    }
    
    .purchase-form label {
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
    }    /* Product item layout */
    .product-item {
        background-color: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.5rem;
        margin-bottom: 0.5rem;
    }    /* Single row layout for products */
    .product-row {
        display: flex;
        gap: 0.25rem;
        align-items: flex-end;
        width: 100%;
    }    .category-field {
        width: 25%;
    }

    .product-field {
        width: 35%;
    }

    .unit-field {
        width: 0%;
        display: none;
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

<div class="flex flex-col max-w-7xl mx-auto">
    <!-- Purchase Form -->
    <div class="purchase-form bg-white rounded-xl shadow p-2 md:p-4 w-full overflow-hidden">
        <h1 class="text-lg font-bold mb-3 text-gray-800">Admin Purchase Form Copy</h1>
        @if(session('success'))
            <div class="mb-4 rounded bg-green-100 border border-green-200 text-green-700 px-4 py-3 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        <form method="POST" action="{{ route('purchases.store') }}" x-data="purchaseForm()" @submit.prevent="submitForm">
            @csrf
            <input type="hidden" name="from_admin_copy" value="1">
            <div class="space-y-2">
                <!-- Date and Vendor Selection -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-medium mb-0.5">Purchase Date <span class="text-red-500">*</span></label>
                        <input type="date" name="purchase_date" x-model="purchaseDate" class="block w-full rounded border border-gray-300 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-400" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-0.5">Vendor <span class="text-red-500">*</span></label>
                        <select name="vendor_id" x-model="vendorId" class="block w-full rounded border border-gray-300 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-400" required>
                            <option value="">Select Vendor</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Products Section --><div class="space-y-2">
                    <template x-for="(item, idx) in items" :key="idx">
                        <div class="product-item">
                            <!-- Single Row: Product, Unit, Quantity, Unit Price, Total, Remove Button -->                            <div class="product-row">
                                <div class="category-field">
                                    <label class="block text-xs font-medium mb-0.5">Category</label>
                                    <select class="block w-full rounded border border-gray-300 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-400"
                                        x-model="item.category_id">
                                        <option value="">All Categories</option>
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
                                            <option :value="product.id" x-text="product.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="unit-field" style="display: none;">
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
                    <div class="bg-blue-50 rounded p-2 min-w-[150px] text-center">
                        <span class="font-semibold text-xs">Grand Total: </span>
                        <span class="font-bold text-sm text-blue-800" x-text="(subtotal - discount).toFixed(2)"></span>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-2">
                    <button type="submit" 
                        class="bg-gray-900 hover:bg-gray-800 text-white font-medium rounded text-xs px-4 py-1.5 transition">
                        Save Purchase
                    </button>                </div>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function purchaseForm() {
    return {
        products: @json($products),
        items: [{ product_id: '', category_id: '', quantity: 1, unit_price: 0, unit_name: '', total: 0 }],
        discount: 0,
        notes: '',
        purchaseDate: '{{ date('Y-m-d') }}',
        vendorId: '',        purchaseId: '',
        editMode: false,
        filteredProducts(idx) {
            const categoryId = parseInt(this.items[idx].category_id);
            return this.products.filter(p => !categoryId || parseInt(p.category_id) === categoryId);
        },
        get subtotal() {
            return this.items.reduce((sum, i) => sum + (i.quantity * i.unit_price || 0), 0);
        },
        get netTotal() {
            return Math.max(0, this.subtotal - (this.discount || 0));
        },
        addItem() {
            this.items.push({ product_id: '', category_id: '', quantity: 1, unit_price: 0, unit_name: '', total: 0 });
        },
        removeItem(idx) {
            if(this.items.length > 1) this.items.splice(idx, 1);
        },
        updateUnit(idx) {
            let prod = this.products.find(p => p.id == this.items[idx].product_id);
            this.items[idx].unit_name = prod ? prod.unit_name : '';
            if (prod) {
                this.items[idx].unit_price = prod.buy_price;
                this.updateTotal(idx);
            }
        },
        updateTotal(idx) {
            let item = this.items[idx];
            item.total = (item.quantity || 0) * (item.unit_price || 0);
        },
        loadPurchase(purchase) {
            this.editMode = true;
            this.purchaseId = purchase.id;
            this.purchaseDate = purchase.purchase_date;
            this.vendorId = purchase.vendor_id;
            this.discount = purchase.discount_amount || 0;
            this.notes = purchase.notes || '';            this.items = purchase.items.map(item => ({
                product_id: item.product_id,
                category_id: this.products.find(p => p.id === item.product_id)?.category_id || '',
                quantity: item.quantity,
                unit_price: item.rate,
                unit_name: item.unit ? item.unit.name : '',
                total: item.amount
            }));
        },
        resetForm() {
            this.editMode = false;
            this.purchaseId = '';
            this.purchaseDate = '{{ date('Y-m-d') }}';
            this.vendorId = '';
            this.discount = 0;
            this.notes = '';
            this.items = [{ product_id: '', category_id: '', quantity: 1, unit_price: 0, unit_name: '', total: 0 }];
        },
        submitForm(e) {
            // Set form fields before submit
            document.querySelector('input[name="purchase_date"]').value = this.purchaseDate;
            document.querySelector('select[name="vendor_id"]').value = this.vendorId;
            document.querySelector('input[name="discount"]').value = this.discount;
            document.querySelector('textarea[name="notes"]').value = this.notes;
            e.target.submit();
        }
    }
}
</script>
@endsection
