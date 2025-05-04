@extends('layouts.app')
@section('content')
    <div class="max-w-7xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold mb-6">Purchases</h1>
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
                <label class="block text-sm font-medium mb-1">Vendor</label>
                <select name="vendor_id" class="form-select w-full">
                    <option value="">All</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" @if(request('vendor_id')==$vendor->id) selected @endif>{{ $vendor->name }}</option>
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
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Purchase # or Vendor" class="form-input w-full" />
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn btn-primary w-full">Filter</button>
            </div>
        </form>
        <div class="bg-white rounded-xl shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">Purchase No</th>
                        <th class="px-4 py-2 text-left">Date</th>
                        <th class="px-4 py-2 text-left">Vendor</th>
                        <th class="px-4 py-2 text-right">Total (Rs.)</th>
                        <th class="px-4 py-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $purchase)
                    <tr>
                        <td class="px-4 py-2">{{ $purchase->purchase_number }}</td>
                        <td class="px-4 py-2">{{ $purchase->purchase_date }}</td>
                        <td class="px-4 py-2">{{ $purchase->vendor->name ?? '-' }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($purchase->total_amount ?? 0, 2) }}</td>
                        <td class="px-4 py-2 text-center">
                            <span class="inline-block px-2 py-1 rounded text-xs {{ $purchase->payment_status=='Paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ $purchase->payment_status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-gray-400">No purchases found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">{{ $purchases->links() }}</div>
        </div>
    </div>
@endsection
