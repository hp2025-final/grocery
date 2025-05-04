@extends('layouts.app')
@section('content')
<div class="flex flex-col md:flex-row md:space-x-6 max-w-7xl mx-auto py-8 px-2">
    <!-- Section A: New Expense Form -->
    <div class="bg-white rounded-xl shadow p-8 w-full md:w-2/5 mb-8 md:mb-0">
        <h2 class="text-2xl font-bold mb-6">New Expense Entry</h2>
        @if(session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
                {{ session('error') }}
            </div>
        @endif
        <form method="POST" action="{{ route('expenses.store') }}" class="space-y-5">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Date <span class="text-red-500">*</span></label>
                <input type="date" name="date" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Expense Account <span class="text-red-500">*</span></label>
                <select name="expense_account_id" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    <option value="">Select Expense Account</option>
                    @foreach($expense_accounts as $account)
                        <option value="{{ $account->account_id }}">{{ $account->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Amount <span class="text-red-500">*</span></label>
                <input type="number" name="amount" min="0.01" step="0.01" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Payment Account <span class="text-red-500">*</span></label>
                <select name="payment_account_id" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    <option value="">Select Payment Account</option>
                    @foreach($banks as $bank)
                        <option value="{{ $bank->account_id }}">{{ $bank->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Description / Notes <span class="text-red-500">*</span></label>
                <textarea name="description" rows="2" maxlength="1000" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>{{ old('description') }}</textarea>
                @error('description')
                    <span class="text-red-600 text-xs">{{ $message }}</span>
                @enderror
            </div>
            <div class="text-right">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow transition">Save Expense</button>
            </div>
        </form>
    </div>
    <!-- Section B: Expenses Table -->
    <div class="bg-white rounded-xl shadow p-8 w-full md:w-3/5">
        <h2 class="text-2xl font-bold mb-6">All Expenses</h2>
        <div x-data="expenseTable()" x-init="init()" class="min-w-full text-sm rounded-xl shadow border border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-3 gap-2">
                <input type="text" x-model="search" @input.debounce.400ms="fetchTable()" placeholder="Search by account or description..." class="w-full md:w-64 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" autocomplete="off">
            </div>
            <div id="expenses-table">
                @include('expenses._expenses_table', ['expenses' => $expenses])
            </div>
        </div>
        <script>
        function expenseTable() {
            return {
                search: '',
                sort: '{{ request('sort', 'created_at') }}',
                direction: '{{ request('direction', 'desc') }}',
                page: 1,
                loading: false,
                init() {
                    // Intercept pagination clicks
                    document.addEventListener('click', (e) => {
                        const pageLink = e.target.closest('[data-page]');
                        if (pageLink) {
                            e.preventDefault();
                            this.setPage(pageLink.getAttribute('data-page'));
                        }
                    });
                },
                setSort(col) {
                    if (this.sort === col) {
                        this.direction = this.direction === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sort = col;
                        this.direction = 'asc';
                    }
                    this.page = 1;
                    this.fetchTable();
                },
                setPage(page) {
                    this.page = page;
                    this.fetchTable();
                },
                fetchTable() {
                    this.loading = true;
                    const params = new URLSearchParams({
                        search: this.search,
                        sort: this.sort,
                        direction: this.direction,
                        page: this.page
                    });
                    fetch(`/expenses/table?${params.toString()}`)
                        .then(r => r.json())
                        .then(data => {
                            document.getElementById('expenses-table').innerHTML = data.html;
                            this.loading = false;
                        });
                },
            }
        }
        </script>
    </div>
</div>
@endsection
