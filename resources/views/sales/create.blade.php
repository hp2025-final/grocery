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

<div class="flex flex-col md:flex-row md:space-x-6 max-w-7xl mx-auto">
    <!-- Sales Form Left -->
    <div class="sales-form bg-white rounded-xl shadow p-4 md:p-8 w-full md:w-2/5 mb-6 md:mb-0 overflow-hidden">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Add New Sale</h1>
        @if(session('success'))
            <div class="mb-4 rounded bg-green-100 border border-green-200 text-green-700 px-4 py-3 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        <form method="POST" action="{{ route('sales.store') }}" x-data="salesForm()" @submit.prevent="submitForm">
            @csrf
            <div class="space-y-4">
                <!-- Date and Customer Selection -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Sale Date <span class="text-red-500">*</span></label>
                        <input type="date" name="sale_date" x-model="saleDate" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Customer <span class="text-red-500">*</span></label>
                        <select name="customer_id" x-model="customerId" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
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
                        Save Sale
                    </button>
                </div>
            </div>
        </form>
    </div>
    <!-- Sales List Right (Section B: Invoice-style All Sales) -->
    <div class="bg-white rounded-xl shadow p-2 sm:p-4 md:p-8 w-full md:w-3/5">
        <h2 class="text-xl font-bold mb-4">All Sales (Invoice View)</h2>
        <form method="get" class="mb-4 flex flex-col md:flex-row md:space-x-4 items-end">
            <div class="mb-2 md:mb-0 w-full md:w-auto">
                <label class="block text-sm font-medium mb-1">Search</label>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Invoice # or Customer" class="form-input w-full text-xs" />
            </div>
            <button type="submit" class="btn btn-primary text-xs w-full md:w-auto">Search</button>
        </form>
        <div class="space-y-4">
            @forelse($sales as $sale)
                <div class="border rounded-lg p-2 sm:p-4 invoice-table">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-2 invoice-header">
                        <div class="mb-2 md:mb-0">
                            <span class="label">Invoice No:</span> <span class="value">{{ $sale->sale_number }}</span><br>
                            <span class="label">Date:</span> <span class="value">{{ $sale->sale_date }}</span><br>
                            <span class="label">Customer:</span> <span class="value">{{ $sale->customer->name ?? '-' }}</span>
                        </div>
                        <div class="flex flex-row md:flex-col justify-between items-center md:items-end">
                            <span class="amount text-right"><span class="label">Total:</span> {{ number_format($sale->net_amount ?? $sale->total_amount ?? 0, 2) }} Rs.</span>
                            <div class="flex space-x-1 mt-1">
                                <a href="{{ route('sales.pdf', $sale->id) }}" class="px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs" title="Export PDF">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="inline w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </a>
                                <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this sale?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto -mx-2 sm:mx-0">
                        <table class="min-w-full border mt-2 text-xs">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-2 sm:px-4 py-2 border text-left">#</th>
                                    <th class="px-2 sm:px-4 py-2 border text-left">Product</th>
                                    <th class="px-2 sm:px-4 py-2 border text-right">Qty</th>
                                    <th class="px-2 sm:px-4 py-2 border text-left">Unit</th>
                                    <th class="px-2 sm:px-4 py-2 border text-right">Rate</th>
                                    <th class="px-2 sm:px-4 py-2 border text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->items as $i => $item)
                                <tr>
                                    <td class="px-2 sm:px-4 py-2 border">{{ $i+1 }}</td>
                                    <td class="px-2 sm:px-4 py-2 border">{{ $item->product->name ?? '-' }}</td>
                                    <td class="px-2 sm:px-4 py-2 border text-right amount">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="px-2 sm:px-4 py-2 border">{{ $item->unit->name ?? '-' }}</td>
                                    <td class="px-2 sm:px-4 py-2 border text-right amount">{{ number_format($item->rate, 2) }}</td>
                                    <td class="px-2 sm:px-4 py-2 border text-right amount">{{ number_format($item->total_amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-400 text-xs">No sales found.</div>
            @endforelse
        </div>
        <div class="mt-4">
            {{ $sales->links() }}
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function salesForm() {
    return {
        products: @json($products),
        items: [{ product_id: '', quantity: 1, unit_price: 0, unit_name: '', total: 0 }],
        discount: 0,
        notes: '',
        saleDate: '{{ date('Y-m-d') }}',
        customerId: '',
        saleId: '',
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
            if (prod) {
                this.items[idx].unit_price = prod.sale_price;
                this.updateTotal(idx);
            }
        },
        updateTotal(idx) {
            let item = this.items[idx];
            item.total = (item.quantity || 0) * (item.unit_price || 0);
        },
        loadSale(sale) {
            this.editMode = true;
            this.saleId = sale.id;
            this.saleDate = sale.sale_date;
            this.customerId = sale.customer_id;
            this.discount = sale.discount_amount || 0;
            this.notes = sale.notes || '';
            this.items = sale.items.map(item => ({
                product_id: item.product_id,
                quantity: item.quantity,
                unit_price: item.rate,
                unit_name: item.unit ? item.unit.name : '',
                total: item.total_amount
            }));
        },
        resetForm() {
            this.editMode = false;
            this.saleId = '';
            this.saleDate = '{{ date('Y-m-d') }}';
            this.customerId = '';
            this.discount = 0;
            this.notes = '';
            this.items = [{ product_id: '', quantity: 1, unit_price: 0, unit_name: '', total: 0 }];
        },
        submitForm(e) {
            // Set form fields before submit
            document.querySelector('input[name="sale_date"]').value = this.saleDate;
            document.querySelector('select[name="customer_id"]').value = this.customerId;
            document.querySelector('input[name="discount"]').value = this.discount;
            document.querySelector('textarea[name="notes"]').value = this.notes;
            e.target.submit();
        }
    }
}
</script>
@endsection
