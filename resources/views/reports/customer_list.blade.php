@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Customer List</h1>
    <form method="get" class="flex flex-wrap items-end gap-4 mb-6">
        <div>
            <label class="block text-sm font-medium">Customer</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search name..." class="form-input" />
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>
    <div class="overflow-x-auto">
        <table class="table-auto w-full border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-2 py-1 border">#</th>
                    <th class="px-2 py-1 border text-left">Customer Name</th>
                    <th class="px-2 py-1 border text-left">Phone</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $i => $customer)
                    <tr>
                        <td class="border px-2 py-1 text-center">{{ ($customers->currentPage() - 1) * $customers->perPage() + $i + 1 }}</td>
                        <td class="border px-2 py-1">{{ $customer->name }}</td>
                        <td class="border px-2 py-1">{{ $customer->phone }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center py-4">No customers found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $customers->links() }}
        </div>
    </div>
</div>
@endsection
