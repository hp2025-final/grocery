@extends('layouts.app')
@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold mb-6">Sales</h1>
        <form method="get" class="mb-6 grid grid-cols-1 md:grid-cols-6 gap-4 bg-white p-4 rounded-lg shadow">
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
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select name="status" class="form-select w-full">
                    <option value="">All</option>
                    <option value="Paid" @if(request('status')=='Paid') selected @endif>Paid</option>
                    <option value="Credit" @if(request('status')=='Credit') selected @endif>Credit</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice # or Customer" class="form-input w-full" />
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn btn-primary w-full">Filter</button>
            </div>
        </form>
        <div class="bg-white rounded-xl shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">Invoice No</th>
                        <th class="px-4 py-2 text-left">Date</th>
                        <th class="px-4 py-2 text-left">Customer</th>
                        <th class="px-4 py-2 text-right">Total (Rs.)</th>
                        <th class="px-4 py-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td class="px-4 py-2">{{ $sale->sale_number }}</td>
                        <td class="px-4 py-2">{{ $sale->sale_date }}</td>
                        <td class="px-4 py-2">{{ $sale->customer->name ?? '-' }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($sale->total_amount ?? 0, 2) }}</td>
                        <td class="px-4 py-2 text-center">
                            <span class="inline-block px-2 py-1 rounded text-xs {{ $sale->payment_status=='Paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ $sale->payment_status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-gray-400">No sales found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">{{ $sales->links() }}</div>
        </div>
    </div>
@endsection
