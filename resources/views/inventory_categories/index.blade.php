@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <h1 class="text-2xl font-bold mb-6">Inventory Categories</h1>
    <div class="mb-4">
        <a href="{{ route('inventory-categories.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add New Category</a>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-2 py-1">Code</th>
                    <th class="px-2 py-1">Name</th>
                    <th class="px-2 py-1">Description</th>
                    <th class="px-2 py-1">Created At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                <tr>
                    <td class="px-2 py-1">{{ $cat->code }}</td>
                    <td class="px-2 py-1">{{ $cat->name }}</td>
                    <td class="px-2 py-1">{{ $cat->description }}</td>
                    <td class="px-2 py-1">{{ $cat->created_at->format('Y-m-d') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-2 py-1 text-center text-gray-500">No categories found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
