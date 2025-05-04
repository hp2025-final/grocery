@extends('layouts.app')
@section('content')
<div class="flex flex-col md:flex-row md:space-x-6 max-w-7xl mx-auto py-8 px-2">
    <!-- Section A: New Bank Transfer Form -->
    <div class="bg-white rounded-xl shadow p-8 w-full md:w-2/5 mb-8 md:mb-0">
        <h2 class="text-2xl font-bold mb-6">New Internal Bank Transfer</h2>
        @if(session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
                {{ session('success') }}
            </div>
        @endif
        <form method="POST" action="{{ route('bank_transfers.store') }}" class="space-y-5">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Date <span class="text-red-500">*</span></label>
                <input type="date" name="date" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" value="{{ old('date', date('Y-m-d')) }}" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">From Bank Account <span class="text-red-500">*</span></label>
                <select name="from_account_id" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    <option value="">Select account</option>
                    @foreach($bankAccounts as $bank)
                        <option value="{{ $bank->id }}" {{ old('from_account_id') == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">To Bank Account <span class="text-red-500">*</span></label>
                <select name="to_account_id" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    <option value="">Select account</option>
                    @foreach($bankAccounts as $bank)
                        <option value="{{ $bank->id }}" {{ old('to_account_id') == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Amount <span class="text-red-500">*</span></label>
                <input type="number" name="amount" min="0.01" step="0.01" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" value="{{ old('amount') }}" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Reference <span class="text-red-500">*</span></label>
                <input type="text" name="description" maxlength="100" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" value="{{ old('description') }}" required>
            </div>
            <div class="text-right">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow transition">Save Transfer</button>
            </div>
        </form>
    </div>
    <!-- Section B: Recent Transfers Table -->
    <div class="bg-white rounded-xl shadow p-8 w-full md:w-3/5">
        <h2 class="text-2xl font-bold mb-6">All Internal Bank Transfers</h2>
        <div class="min-w-full text-sm rounded-xl shadow border border-gray-200">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-2 text-left">Date</th>
                        <th class="px-4 py-2 text-left">From</th>
                        <th class="px-4 py-2 text-left">To</th>
                        <th class="px-4 py-2 text-right">Amount</th>
                        <th class="px-4 py-2 text-left">Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($recentTransfers as $transfer)
                    <tr>
                        <td class="px-4 py-2">{{ $transfer->date }}</td>
                        <td class="px-4 py-2">{{ optional($transfer->fromBank)->name }}</td>
                        <td class="px-4 py-2">{{ optional($transfer->toBank)->name }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($transfer->amount, 2) }}</td>
                        <td class="px-4 py-2">{{ $transfer->description }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
