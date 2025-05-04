@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto py-8 px-4">
    <h1 class="text-2xl font-bold mb-6">Stock Adjustment</h1>
    <form method="POST" action="{{ route('stock-adjustments.store') }}" x-data="stockAdjustmentForm()" @submit.prevent="submitForm" class="bg-white rounded-lg shadow p-6 space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Adjustment Type <span class="text-red-500">*</span></label>
                <select name="adjustment_type" class="form-select w-full" x-model="adjustment_type" required>
                    <option value="">Select Type</option>
                    <option value="Increase">Increase</option>
                    <option value="Decrease">Decrease</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Date <span class="text-red-500">*</span></label>
                <input type="date" name="date" class="form-input w-full" value="{{ date('Y-m-d') }}" required>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Reason / Notes <span class="text-red-500">*</span></label>
            <textarea name="notes" class="form-input w-full" rows="2" maxlength="1000" required></textarea>
        </div>
        <div>
            <h2 class="text-lg font-semibold mb-4">Product Lines <span class="text-red-500">*</span></h2>
            <table class="min-w-full text-sm mb-2">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-2 py-1">Product</th>
                        <th class="px-2 py-1">Quantity</th>
                        <th class="px-2 py-1">Unit</th>
                        <th class="px-2 py-1">Unit Price</th>
                        <th class="px-2 py-1">Total</th>
                        <th class="px-2 py-1"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, idx) in items" :key="idx">
                        <tr>
                            <td class="px-2 py-1">
                                <select :name="`products[${idx}][product_id]`" class="form-select w-full" x-model="item.product_id" @change="updateUnit(idx)" required>
                                    <option value="">Select</option>
                                    <template x-for="product in products" :key="product.id">
                                        <option :value="product.id" x-text="product.name"></option>
                                    </template>
                                </select>
                            </td>
                            <td class="px-2 py-1">
                                <input type="number" min="0.01" step="0.01" :name="`products[${idx}][quantity]`" class="form-input w-20" x-model.number="item.quantity" @input="updateTotal(idx)" required>
                            </td>
                            <td class="px-2 py-1">
                                <input type="text" class="form-input w-20 bg-gray-100" :value="item.unit_name" readonly>
                            </td>
                            <td class="px-2 py-1">
                                <input type="number" min="0" step="0.01" :name="`products[${idx}][unit_price]`" class="form-input w-24" x-model.number="item.unit_price" @input="updateTotal(idx)" required>
                            </td>
                            <td class="px-2 py-1">
                                <input type="text" class="form-input w-24 bg-gray-100 text-right" :value="item.total.toFixed(2)" readonly>
                            </td>
                            <td class="px-2 py-1">
                                <button type="button" class="text-red-500 hover:text-red-700" @click="removeItem(idx)">&times;</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <button type="button" class="btn btn-outline-primary mt-2" @click="addItem">+ Add Product</button>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex flex-col gap-2 md:flex-row md:gap-8">
                <div class="flex-1 flex justify-between font-bold text-lg"><span>Grand Total:</span> <span x-text="grandTotal.toFixed(2)"></span></div>
            </div>
        </div>
        <div class="text-right">
            <button type="submit" class="btn btn-primary px-8 py-2">Save Adjustment</button>
        </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function stockAdjustmentForm() {
    return {
        products: @json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'unit_name' => $p->unit->name])->values()),
        items: [{ product_id: '', quantity: 1, unit_price: 0, unit_name: '', total: 0 }],
        adjustment_type: '',
        get grandTotal() {
            return this.items.reduce((sum, i) => sum + (i.quantity * i.unit_price || 0), 0);
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
        },
        updateTotal(idx) {
            let item = this.items[idx];
            item.total = (item.quantity || 0) * (item.unit_price || 0);
        },
        submitForm(e) {
            // Let browser handle validation
            e.target.submit();
        }
    }
}
</script>
@endsection
