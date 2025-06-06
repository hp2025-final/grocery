@extends('layouts.app')
@section('content')
<div class="max-w-5xl mx-auto py-8 px-2">
    <h1 class="text-2xl font-bold mb-6">Bank Ledger: {{ $bank->name }}</h1>
    <div class="bg-white rounded-xl shadow p-6">
        <div x-data="bankLedgerTable()" x-init="init()" class="min-w-full text-sm rounded-xl shadow border border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-3 gap-2">
                <div class="flex flex-row gap-2 items-center">
                    <label class="text-sm font-medium">From</label>
                    <input type="date" x-model="from" class="px-2 py-1 border border-gray-300 rounded">
                    <label class="text-sm font-medium">To</label>
                    <input type="date" x-model="to" class="px-2 py-1 border border-gray-300 rounded">
                    <button type="button" @click="fetchTable()" class="ml-2 px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 transition">Filter</button>
                </div>
                <input type="text" x-model="search" @input.debounce.400ms="fetchTable()" placeholder="Search by account, description, reference..." class="w-full md:w-64 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-400" autocomplete="off">
            </div>
            <div id="bank-ledger-table">
                @include('banks._ledger_table', ['entries' => $entries, 'openingBalance' => $openingBalance ?? null, 'from' => $from, 'bank' => $bank])
            </div>
        </div>
        <script>
        function bankLedgerTable() {
            return {
                search: '',
                from: '',
                to: '',
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
                    const params = new URLSearchParams({
                        search: this.search,
                        page: this.page,
                        from: this.from,
                        to: this.to
                    });
                    fetch(`/banks/{{ $bank->id }}/ledger/filter?${params.toString()}`)
                        .then(r => r.json())
                        .then(data => {
                            document.getElementById('bank-ledger-table').innerHTML = data.html;
                            this.loading = false;
                        });
                },
            }
        }
        </script>
    </div>
</div>
@endsection
