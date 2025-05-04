@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <h1 class="text-2xl font-bold mb-6">Stock Adjustments</h1>
    <div class="bg-white rounded-lg shadow p-6">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-2 py-1">#</th>
                    <th class="px-2 py-1">Date</th>
                    <th class="px-2 py-1">Type</th>
                    <th class="px-2 py-1">User</th>
                    <th class="px-2 py-1">Notes</th>
                    <th class="px-2 py-1">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($adjustments as $adj)
                <tr>
                    <td class="px-2 py-1">{{ $adj->adjustment_number ?? $adj->id }}</td>
                    <td class="px-2 py-1">{{ $adj->adjustment_date ?? $adj->date }}</td>
                    <td class="px-2 py-1">{{ $adj->adjustment_type }}</td>
                    <td class="px-2 py-1">{{ $adj->user->name ?? '-' }}</td>
                    <td class="px-2 py-1">{{ $adj->notes }}</td>
                    <td class="px-2 py-1 text-right">{{ number_format($adj->total_amount, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-gray-500">No stock adjustments found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $adjustments->links() }}</div>
    </div>
</div>
@endsection
