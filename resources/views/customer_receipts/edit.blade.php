@extends('layouts.app')
@section('content')
<div class="flex flex-col md:flex-row gap-8 justify-center mt-8">
    <div class="bg-white rounded-xl shadow p-8 w-full md:w-2/3 lg:w-1/2">
        <h2 class="text-xl font-bold mb-6">Edit Customer Receipt</h2>
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                {{ session('error') }}
            </div>
        @endif
        <form action="{{ route('customer-receipts.update', $receipt->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block font-semibold mb-1">Customer</label>
                <select name="customer_id" class="w-full border rounded px-4 py-2" required>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ $receipt->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1">Receipt Date</label>
                <input type="date" name="receipt_date" value="{{ old('receipt_date', $receipt->receipt_date) }}" class="w-full border rounded px-4 py-2" required>
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1">Amount Received</label>
                <input type="number" step="0.01" name="amount_received" value="{{ old('amount_received', $receipt->amount_received) }}" class="w-full border rounded px-4 py-2" required>
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1">Payment Account</label>
                <select name="payment_account_id" class="w-full border rounded px-4 py-2" required>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" {{ $receipt->payment_account_id == $account->id ? 'selected' : '' }}>{{ $account->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1">Reference <span class="text-red-500">*</span></label>
                <input type="text" name="reference" value="{{ old('reference', $receipt->reference) }}" class="w-full border rounded px-4 py-2" required>
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1">Notes</label>
                <textarea name="notes" class="w-full border rounded px-4 py-2">{{ old('notes', $receipt->notes) }}</textarea>
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('customer-receipts.create') }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 rounded font-semibold">Cancel</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded font-semibold">Update Receipt</button>
            </div>
        </form>
    </div>
</div>
@endsection
