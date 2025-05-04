@extends('layouts.app')
@section('content')
<!--
  IMPORTANT: Controller must provide $payments (paginated) for the table below!
  Example: $payments = VendorPayment::with('vendor','account')->latest()->paginate(10);
-->
<div class="flex flex-col md:flex-row md:space-x-6 max-w-7xl mx-auto py-8 px-2">
    <!-- Section A: New Payment Form -->
    <div class="bg-white rounded-xl shadow p-8 w-full md:w-2/5 mb-8 md:mb-0">
        <h2 class="text-2xl font-bold mb-6">New Vendor Payment</h2>
        @if(session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
                {{ session('success') }}
            </div>
        @endif
        <form id="vendor-payment-form" method="POST" action="{{ route('vendor-payments.store') }}" class="space-y-5">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Date <span class="text-red-500">*</span></label>
                <input type="date" name="payment_date" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" value="{{ old('payment_date', date('Y-m-d')) }}" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Vendor Name <span class="text-red-500">*</span></label>
                <select name="vendor_id" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    <option value="">Select Vendor</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Amount Paid <span class="text-red-500">*</span></label>
                <input type="number" name="amount_paid" min="0.01" step="0.01" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" value="{{ old('amount_paid') }}" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Payment Account <span class="text-red-500">*</span></label>
                <select name="payment_account_id" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    <option value="">Select Bank</option>
                    @foreach($bankAccounts as $account)
                        <option value="{{ $account->id }}" {{ old('payment_account_id') == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" name="payment_method" value="bank">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Reference <span class="text-red-500">*</span></label>
                <input type="text" name="reference" maxlength="100" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" value="{{ old('reference') }}" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Notes <span class="text-red-500">*</span></label>
                <textarea name="notes" rows="2" maxlength="1000" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>{{ old('notes') }}</textarea>
            </div>
            <div class="text-right">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow transition">Record Payment</button>
            </div>
        </form>
    </div>
    <!-- Section B: Payments Table -->
    <div class="bg-white rounded-xl shadow p-8 w-full md:w-3/5">
    <h2 class="text-2xl font-bold mb-6">All Vendor Payments</h2>
    <div x-data="vendorPaymentsTable()" x-init="init()" class="min-w-full text-sm rounded-xl shadow border border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-3 gap-2">
            <input type="text" x-model="search" @input.debounce.400ms="fetchTable()" placeholder="Search by vendor name..." class="w-full md:w-64 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" autocomplete="off">
        </div>
        <div id="payments-table">
            @include('vendor_payments._payments_table', ['payments' => $payments])
        </div>
    </div>
    <script>
    function vendorPaymentsTable() {
        return {
            search: '',
            page: 1,
            loading: false,
            init() {
                document.addEventListener('click', (e) => {
                    if (e.target.closest('[data-page]')) {
                        e.preventDefault();
                        this.page = e.target.closest('[data-page]').getAttribute('data-page');
                        this.fetchTable();
                    }
                });
            },
            fetchTable() {
                this.loading = true;
                fetch(`/vendor-payments/live-search?search=${encodeURIComponent(this.search)}&page=${this.page}`)
                    .then(r => r.json())
                    .then(data => {
                        document.getElementById('payments-table').innerHTML = data.html;
                        this.loading = false;
                    });
            },
        }
    }
    </script>
</div>
</div>
@endsection
