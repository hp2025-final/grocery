@extends('layouts.app')
@section('content')
<style>
    /* Base styles */
    .purchase-form input,
    .purchase-form select,
    .purchase-form textarea,
    .purchase-form label {
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

<div class="flex flex-col md:flex-row md:space-x-6 max-w-7xl mx-auto py-8 px-2">
    <!-- Purchase Form Left -->
    <div class="purchase-form bg-white rounded-xl shadow p-4 md:p-8 w-full md:w-2/5 mb-6 md:mb-0 overflow-hidden">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Add New Purchase</h1>
        @if(session('success'))
            <div class="mb-4 rounded bg-green-100 border border-green-200 text-green-700 px-4 py-3 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        
        <form method="POST" action="{{ route('purchases.store') }}" x-data="purchaseForm()" @submit.prevent="submitForm">
            @csrf
            <div class="space-y-4">
                <!-- Date and Vendor Selection -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                <label class="block text-sm font-medium mb-1">Purchase Date <span class="text-red-500">*</span></label>
                <input type="date" name="purchase_date" x-model="purchaseDate" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            </div>
                    <div>
                <label class="block text-sm font-medium mb-1">Vendor <span class="text-red-500">*</span></label>
                <select name="vendor_id" x-model="vendorId" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    <option value="">Select Vendor</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
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
                <input type="number" min="0" step="0.01" name="discount" x-model.number="discount"
                    class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Notes <span class="text-red-500">*</span></label>
                <textarea name="notes" rows="2" x-model="notes"
                            class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required></textarea>
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
                    <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold rounded px-8 py-2 transition">
                        Save Purchase
                    </button>
                </div>
            </div>
        </form>
    </div>
    <!-- Purchases List Right (Section B: Invoice-style All Purchases) -->
    <div class="bg-white rounded-xl shadow p-2 sm:p-4 md:p-8 w-full md:w-3/5">
        <h2 class="text-xl font-bold mb-4">All Purchases (Invoice View)</h2>
        <form method="get" class="mb-6 flex flex-col md:flex-row md:space-x-4 items-end">
            <div class="mb-2 md:mb-0 w-full md:w-auto flex-grow">
                <label class="block text-sm font-medium mb-1 text-gray-700">Search</label>
                <input type="text" name="search" value="{{ $search ?? '' }}" 
                    placeholder="Search by invoice # or vendor" 
                    class="form-input w-full rounded-lg border-gray-300 shadow-sm text-sm" />
            </div>
            <button type="submit" class="btn bg-gray-900 hover:bg-gray-800 text-white px-6 py-2 rounded-lg text-sm transition-colors duration-200">
                Search
            </button>
        </form>
        <div class="space-y-4">
            @forelse($purchases as $purchase)
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 border border-gray-100">
                    <!-- Action Buttons -->
                    <div class="px-4 py-2 bg-gray-50 flex justify-between items-center border-b">
                        <span class="text-sm font-medium text-gray-600 border-r border-gray-200 pr-4">#{{ $purchase->purchase_number }}</span>
                        <div class="flex space-x-2">
                            <a href="{{ route('purchases.show', $purchase->id) }}" 
                               class="p-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors duration-200"
                               title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('purchases.pdf', $purchase->id) }}" 
                               class="p-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors duration-200"
                               target="_blank"
                               title="Print">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                            </a>
                            <form action="{{ route('purchases.destroy', $purchase->id) }}" method="POST" class="inline-block" 
                                  onsubmit="return confirm('Are you sure you want to delete this purchase?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors duration-200"
                                        title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    <!-- Purchase Details -->
                    <div class="p-4 flex justify-between items-center divide-x divide-gray-200">
                        <div class="flex items-center">
                            <span class="text-sm text-gray-600 px-4">{{ date('d-m-y', strtotime($purchase->purchase_date)) }}</span>
                            <span class="text-sm text-gray-600 px-4 border-l border-gray-200">{{ $purchase->vendor->name ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-bold text-gray-600 px-4">Rs. {{ number_format($purchase->net_amount ?? $purchase->total_amount ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-sm text-gray-500 bg-gray-50 rounded-lg border border-gray-100">
                    No purchases found.
                </div>
            @endforelse
        </div>
        <div class="mt-4">
            {{ $purchases->links() }}
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function purchaseForm() {
    return {
        products: @json($products),
        items: [{ product_id: '', quantity: 1, unit_price: 0, unit_name: '', total: 0 }],
        discount: 0,
        notes: '',
        purchaseDate: '{{ date('Y-m-d') }}',
        vendorId: '',
        purchaseId: '',
        editMode: false,
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
            if (prod && prod.buy_price != null) {
                this.items[idx].unit_price = prod.buy_price;
            }
            this.updateTotal(idx);
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
            this.discount = purchase.discount || 0;
            this.notes = purchase.notes || '';
            this.items = purchase.items.map(item => ({
                product_id: item.product_id,
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
            this.items = [{ product_id: '', quantity: 1, unit_price: 0, unit_name: '', total: 0 }];
        },
        submitForm(e) {
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
