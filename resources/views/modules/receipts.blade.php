@extends('layouts.app')
@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold mb-6">Customer Receipts</h1>
        <form method="get" class="mb-6 grid grid-cols-1 md:grid-cols-5 gap-4 bg-white p-4 rounded-lg shadow">
            <div>
                <label class="block text-sm font-medium mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-input w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-input w-full" />
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Customer</label>
                <select name="customer_id" class="form-select w-full">
                    <option value="">All</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" @if(request('customer_id')==$customer->id) selected @endif>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Receipt # or Customer" class="form-input w-full" />
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn btn-primary w-full">Filter</button>
            </div>
        </form>
        <div class="bg-white rounded-xl shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">Receipt No</th>
                        <th class="px-4 py-2 text-left">Date</th>
                        <th class="px-4 py-2 text-right">Amount (Rs.)</th>
                        <th class="px-4 py-2 text-left">Customer</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipts as $receipt)
                    <tr>
                        <td class="px-4 py-2">{{ $receipt->receipt_number }}</td>
                        <td class="px-4 py-2">{{ $receipt->receipt_date }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($receipt->amount_received ?? 0, 2) }}</td>
                        <td class="px-4 py-2">{{ $receipt->customer->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-4 text-gray-400">No receipts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">{{ $receipts->links() }}</div>
        </div>
    </div>
@endsection
