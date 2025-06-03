@extends('layouts.app')
@section('content')
<style>
    /* Base styles */
    .receipt-form input,
    .receipt-form select,
    .receipt-form textarea {
        font-size: 0.75rem;
        padding-top: 0.35rem;
        padding-bottom: 0.35rem;
        height: 2rem;
    }
    
    .receipt-form label {
        font-size: 0.7rem;
        margin-bottom: 0.25rem;
    }

    .entry-card {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
        position: relative;
    }

    .entry-number {
        position: absolute;
        top: -0.75rem;
        left: 1rem;
        background: #fff;
        padding: 0 0.5rem;
        font-size: 0.75rem;
        color: #6b7280;
    }

    .remove-entry {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
    }
</style>

<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">
    <!-- Section A: New Receipt Form -->
    <div class="receipt-form bg-white rounded-xl shadow p-4 md:p-6 w-full overflow-hidden">
        <h1 class="text-lg font-bold mb-3 text-gray-800">New Customer Receipts</h1>
        @if(session('success'))
            <div class="mb-4 rounded bg-green-100 border border-green-200 text-green-700 px-4 py-3 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <form id="customer-receipt-form" action="{{ route('customer-receipts.store') }}" method="POST" x-data="receiptForm()" class="max-w-7xl mx-auto">
            @csrf
            <div class="space-y-4">
                <template x-for="(entry, index) in entries" :key="index">
                    <div class="entry-card">
                        <div class="entry-number" x-text="'Entry #' + (index + 1)"></div>
                        <button type="button" 
                                class="remove-entry text-red-500 hover:text-red-700" 
                                @click="removeEntry(index)"
                                x-show="entries.length > 1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>

                        <!-- All fields in one row -->
                        <div class="grid grid-cols-12 gap-2">
                            <!-- Date (15%) -->
                            <div class="col-span-2">
                                <label class="block text-xs font-medium mb-0.5">Date <span class="text-red-500">*</span></label>
                                <input type="date" :name="'entries['+index+'][receipt_date]'" class="block w-full rounded border border-gray-300 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-400" x-model="entry.receipt_date" required>
                            </div>

                            <!-- Customer (30%) -->
                            <div class="col-span-4">
                                <label class="block text-xs font-medium mb-0.5">Customer <span class="text-red-500">*</span></label>
                                <select :name="'entries['+index+'][customer_id]'" class="block w-full rounded border border-gray-300 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-400" x-model="entry.customer_id" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Amount (15%) -->
                            <div class="col-span-2">
                                <label class="block text-xs font-medium mb-0.5">Amount <span class="text-red-500">*</span></label>
                                <input type="number" :name="'entries['+index+'][amount_received]'" min="0.01" step="0.01" class="block w-full rounded border border-gray-300 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-400" x-model="entry.amount_received" required>
                            </div>

                            <!-- Payment Account (20%) -->
                            <div class="col-span-2">
                                <label class="block text-xs font-medium mb-0.5">Account <span class="text-red-500">*</span></label>
                                <select :name="'entries['+index+'][payment_account_id]'" class="block w-full rounded border border-gray-300 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-400" x-model="entry.payment_account_id" required>
                                    <option value="">Select Bank</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Notes (20%) -->
                            <div class="col-span-2">
                                <label class="block text-xs font-medium mb-0.5">Notes <span class="text-red-500">*</span></label>
                                <input type="text" :name="'entries['+index+'][notes]'" maxlength="1000" class="block w-full rounded border border-gray-300 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-400" x-model="entry.notes" required>
                            </div>

                            <input type="hidden" :name="'entries['+index+'][payment_method]'" value="bank">
                        </div>
                    </div>
                </template>

                <!-- Add Entry and Submit Buttons in a row -->
                <div class="flex justify-between items-center pt-2">
                    <button type="button" 
                            @click="addEntry()"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-medium rounded text-xs px-4 py-1.5 transition flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Add Entry
                    </button>

                    <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white font-medium rounded text-xs px-4 py-1.5 transition">
                        Save Receipts
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Section B: Receipts Table -->
    <div class="bg-white rounded-xl shadow p-4 md:p-6 w-full">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-lg font-bold mb-4">All Customer Receipts</h2>
            <div x-data="receiptTable()" x-init="init()" class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-3 gap-2">
                    <div class="flex items-center gap-2">
                        <input type="text" 
                            x-model="search" 
                            @input.debounce.400ms="fetchTable()" 
                            placeholder="Search by customer name..." 
                            class="w-full md:w-64 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-400" 
                            autocomplete="off">
                        
                        <a href="{{ route('customer-receipts.export-table') }}" 
                           class="bg-gray-900 hover:bg-gray-800 text-white font-medium rounded text-xs px-3 py-1.5 transition inline-flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export Table
                        </a>
                    </div>
                </div>
                <div id="receipts-table">
                    @include('customer_receipts._receipts_table', ['receipts' => $receipts])
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function receiptForm() {
    return {
        entries: [
            {
                receipt_date: new Date().toISOString().split('T')[0],
                customer_id: '',
                amount_received: '',
                payment_account_id: '',
                notes: ''
            }
        ],
        addEntry() {
            this.entries.push({
                receipt_date: new Date().toISOString().split('T')[0],
                customer_id: '',
                amount_received: '',
                payment_account_id: '',
                notes: ''
            });
        },
        removeEntry(index) {
            if (this.entries.length > 1) {
                this.entries.splice(index, 1);
            }
        }
    }
}

function receiptTable() {
    return {
        search: '',

        page: 1,
        loading: false,
        init() {
            // Listen for pagination clicks
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
            fetch(`/customer-receipts/live-search?search=${encodeURIComponent(this.search)}&page=${this.page}`)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('receipts-table').innerHTML = data.html;
                    this.loading = false;
                });
        },

    }
}
</script>
@endsection
