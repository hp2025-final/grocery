@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto py-8">
    <div class="flex flex-col md:flex-row gap-8">
        <div class="md:w-1/3 w-full">
            @if(session('success'))
                <div class="mb-4 p-3 rounded bg-green-100 text-green-800 border border-green-300">
                    {{ session('success') }}
                </div>
            @endif
            <h1 class="text-2xl font-bold mb-6">Create Inventory Category</h1>
@if(isset($category))
    <form method="POST" action="{{ route('inventory-categories.update', $category->id) }}" class="bg-white p-6 rounded shadow">
        @csrf
        @method('PUT')
        <input type="hidden" name="code" value="{{ $category->code }}" />
        <div class="mb-4">
            <label class="block font-semibold mb-1">Code</label>
            <input type="text" name="code_display" value="{{ $category->code }}" readonly class="w-full border-gray-300 rounded px-3 py-2 bg-gray-100" />
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Category Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $category->name) }}" class="w-full border-gray-300 rounded px-3 py-2" required />
            @error('name')
                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Description</label>
            <textarea name="description" class="w-full border-gray-300 rounded px-3 py-2">{{ old('description', $category->description) }}</textarea>
            @error('description')
                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Update Category</button>
        <a href="{{ route('inventory-categories.create') }}" class="ml-2 text-gray-500 hover:underline">Cancel</a>
    </form>
@else
    <form method="POST" action="{{ url('/inventory-categories') }}" class="bg-white p-6 rounded shadow">
        @csrf
        <div class="mb-4">
            <label class="block font-semibold mb-1">Code</label>
            <input type="text" name="code" value="{{ old('code', $nextCode) }}" readonly class="w-full border-gray-300 rounded px-3 py-2 bg-gray-100" />
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Category Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full border-gray-300 rounded px-3 py-2" required />
            @error('name')
                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Description</label>
            <textarea name="description" class="w-full border-gray-300 rounded px-3 py-2">{{ old('description') }}</textarea>
            @error('description')
                <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Create Category</button>
    </form>
@endif
        </div>
        <div class="md:w-2/3 w-full">
            <h2 class="text-xl font-semibold mb-4">All Inventory Categories</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 shadow rounded-lg overflow-hidden">
    <thead class="bg-blue-50">
        <tr>
            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">#</th>
            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Code</th>
            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Name</th>
            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Description</th>
            
            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-100">
        @forelse ($allCategories as $i => $cat)
            <tr class="hover:bg-blue-50 transition">
                <td class="px-3 py-2 text-center text-sm text-gray-800">{{ $i + 1 }}</td>
                <td class="px-3 py-2 text-sm text-gray-800">{{ $cat->code }}</td>
                <td class="px-3 py-2 text-sm text-gray-800">{{ $cat->name }}</td>
                <td class="px-3 py-2 text-sm text-gray-800">{{ $cat->description }}</td>
                
                <td class="px-3 py-2 text-sm flex gap-2">
                    <a href="{{ route('inventory-categories.edit', $cat->id) }}" class="inline-block px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded text-xs font-semibold transition">Edit</a>
<form action="{{ route('inventory-categories.destroy', $cat->id) }}" method="POST" class="inline">
    @csrf
    @method('DELETE')
    <button type="submit" class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-xs font-semibold transition" onclick="return confirm('Delete this category?')">Delete</button>
</form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center py-4 text-gray-500">No categories found.</td></tr>
        @endforelse
    </tbody>
</table>
            </div>
        </div>
    </div>
</div>
@endsection
