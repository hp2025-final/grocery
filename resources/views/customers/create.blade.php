@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto py-8">
    <div class="flex flex-col md:flex-row gap-8" x-data="customerForm()">
        <!-- Left: Customer Form -->
        <div class="md:w-1/3 w-full">
            <div class="bg-white p-6 rounded shadow">
                <h2 class="text-xl font-semibold mb-4">Add New Customer</h2>
                @if(session('success'))
                    <div class="mb-4 p-3 rounded bg-green-100 border border-green-400 text-green-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-4 p-3 rounded bg-red-100 border border-red-400 text-red-800">
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" :action="editMode ? updateUrl : createUrl" class="space-y-4">
                    @csrf
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    @csrf
                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="name" required class="w-full border-gray-300 rounded px-3 py-2" />
                    </div>
                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Phone</label>
                        <input name="phone" type="text" x-model="phone" class="w-full border-gray-300 rounded px-3 py-2" />
                        @error('phone')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block font-semibold mb-1">Opening Balance</label>
                        <input type="number" name="opening_balance" x-model="opening_balance" step="any" class="w-full border-gray-300 rounded px-3 py-2" />
                    </div>
                    <div class="mb-6">
                        <label class="block font-semibold mb-1">Opening Balance Type</label>
                        <select name="opening_type" x-model="opening_type" class="w-full border-gray-300 rounded px-3 py-2" required>
                            <option value="debit">Debit</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" x-text="editMode ? 'Update Customer' : 'Save Customer'"></button>
                        <template x-if="editMode">
                            <button type="button" @click="resetForm()" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400 ml-2">Cancel</button>
                        </template>
                    </div>
                </form>
            </div>
        </div>
        <!-- Right: Customer List Table -->
        <div class="md:w-2/3 w-full">
            <h2 class="text-xl font-semibold mb-4">All Customers</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 shadow rounded-lg overflow-hidden">
                    <thead class="bg-blue-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">#</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Name</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Phone</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Opening Balance</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Type</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Ledger</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($allCustomers as $i => $customer)
                            <tr class="hover:bg-blue-50 transition">
                                <td class="px-3 py-2 text-center text-sm text-gray-800">{{ $i + 1 }}</td>
                                <td class="px-3 py-2 text-sm text-gray-800">{{ $customer->name }}</td>
                                <td class="px-3 py-2 text-sm text-gray-800">{{ $customer->phone }}</td>
                                <td class="px-3 py-2 text-sm text-gray-800">{{ number_format($customer->opening_balance, 2) }}</td>
                                <td class="px-3 py-2 text-sm text-gray-800 text-capitalize">{{ ucfirst($customer->opening_type) }}</td>
                                <td class="px-3 py-2 text-center flex gap-2 justify-center">
                                    <a href="{{ route('customers.ledger', ['id' => $customer->id]) }}" class="inline-block px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-full text-xs font-semibold transition" title="Ledger">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="inline w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4h9" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                                        Ledger
                                    </a>
                                    <button type="button"
                                        class="inline-block px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded text-xs font-semibold transition"
                                        @click="editCustomer({
                                            id: {{ $customer->id }},
                                            name: `{{ addslashes($customer->name) }}`,
                                            phone: `{{ addslashes($customer->phone) }}`,
                                            opening_balance: {{ $customer->opening_balance ?? 0 }},
                                            opening_type: `{{ $customer->opening_type ?? 'credit' }}`
                                        })">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-gray-500">No customers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<script>
function customerForm() {
    return {
        editMode: false,
        id: null,
        name: '',
        phone: '',
        opening_balance: '',
        opening_type: 'debit',
        createUrl: '{{ route('customers.store') }}',
        updateUrl: '',
        editCustomer(customer) {
            this.editMode = true;
            this.id = customer.id;
            this.name = customer.name;
            this.phone = customer.phone;
            this.opening_balance = customer.opening_balance;
            this.opening_type = customer.opening_type;
            this.updateUrl = `/customers/${customer.id}`;
        },
        resetForm() {
            this.editMode = false;
            this.id = null;
            this.name = '';
            this.phone = '';
            this.opening_balance = '';
            this.opening_type = 'debit';
            this.updateUrl = '';
        },
    }
}
</script>
</div>
@endsection
