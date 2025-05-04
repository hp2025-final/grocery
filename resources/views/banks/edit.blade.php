@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto mt-8 bg-white rounded-xl shadow p-8">
    <h2 class="text-2xl font-bold mb-6">Edit Bank</h2>
    @if(session('success'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('banks.update', $bank->id) }}" method="POST">
        @csrf
        @method('PUT')
        @csrf
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium mb-1">Bank Name <span class="text-red-500">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name', $bank->name) }}" required class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <div class="mb-4">
            <label for="account_title" class="block text-sm font-medium mb-1">Account Title <span class="text-red-500">*</span></label>
            <input type="text" id="account_title" name="account_title" value="{{ old('account_title', $bank->account_title) }}" required class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <div class="mb-4">
            <label for="account_number" class="block text-sm font-medium mb-1">Account Number <span class="text-red-500">*</span></label>
            <input type="text" id="account_number" name="account_number" value="{{ old('account_number', $bank->account_number) }}" required class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <div class="mb-4">
            <label for="opening_balance" class="block text-sm font-medium mb-1">Opening Balance</label>
            <input type="number" step="0.01" id="opening_balance" name="opening_balance" value="{{ old('opening_balance', $bank->opening_balance) }}" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <div class="mb-4">
            <label for="branch" class="block text-sm font-medium mb-1">Branch</label>
            <input type="text" id="branch" name="branch" value="{{ old('branch', $bank->branch) }}" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <div class="mb-4">
            <label for="iban" class="block text-sm font-medium mb-1">IBAN</label>
            <input type="text" id="iban" name="iban" value="{{ old('iban', $bank->iban) }}" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <div class="mb-4">
            <label for="swift_code" class="block text-sm font-medium mb-1">SWIFT Code</label>
            <input type="text" id="swift_code" name="swift_code" value="{{ old('swift_code', $bank->swift_code) }}" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <div class="mb-4">
            <label for="notes" class="block text-sm font-medium mb-1">Notes <span class="text-red-500">*</span></label>
            <textarea id="notes" name="notes" rows="2" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required>{{ old('notes', $bank->notes) }}</textarea>
        </div>
        <div class="text-right flex gap-2 justify-end">
            <a href="{{ route('banks.create') }}" class="px-8 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold rounded transition">Cancel</a>
            <button type="submit" class="px-8 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded transition">Update Bank</button>
        </div>
    </form>
</div>
@endsection
