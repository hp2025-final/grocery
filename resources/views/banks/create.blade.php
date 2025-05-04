@extends('layouts.app')

@section('content')
@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="flex flex-col md:flex-row md:space-x-6 max-w-7xl mx-auto py-8 px-2">
    <!-- Section A: Bank Form -->
    <div class="bg-white rounded-xl shadow p-8 w-full md:w-2/5 mb-8 md:mb-0">
        @if(session('success') || isset($success))
            <div class="mb-4 rounded bg-green-100 border border-green-200 text-green-700 px-4 py-3 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') ?? $success }}</span>
            </div>
        @endif
        <h2 class="text-2xl font-bold mb-6">Add New Bank</h2>
        <form action="{{ route('banks.store') }}" method="POST" class="space-y-0">
            @csrf
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium mb-1">Bank Name <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" required class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="mb-4">
                <label for="account_title" class="block text-sm font-medium mb-1">Account Title <span class="text-red-500">*</span></label>
                <input type="text" id="account_title" name="account_title" required class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="mb-4">
                <label for="account_number" class="block text-sm font-medium mb-1">Account Number <span class="text-red-500">*</span></label>
                <input type="text" id="account_number" name="account_number" required class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="mb-4">
                <label for="opening_balance" class="block text-sm font-medium mb-1">Opening Balance</label>
                <input type="number" step="0.01" id="opening_balance" name="opening_balance" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="mb-4">
                <label for="branch" class="block text-sm font-medium mb-1">Branch</label>
                <input type="text" id="branch" name="branch" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="mb-4">
                <label for="iban" class="block text-sm font-medium mb-1">IBAN</label>
                <input type="text" id="iban" name="iban" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="mb-4">
                <label for="swift_code" class="block text-sm font-medium mb-1">SWIFT Code</label>
                <input type="text" id="swift_code" name="swift_code" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="mb-4">
                <label for="notes" class="block text-sm font-medium mb-1">Notes <span class="text-red-500">*</span></label>
                <textarea id="notes" name="notes" rows="2" class="block w-full rounded border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400" required></textarea>
            </div>
            <div class="text-right md:text-right">
                <button type="submit" class="px-8 py-2 w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded transition">Add Bank</button>
            </div>
        </form>
    </div>
    <!-- Section B: Bank List/Report Placeholder -->
    <div class="bg-white rounded-xl shadow p-8 w-full md:w-3/5">
    <h2 class="text-xl font-bold mb-4">Bank Accounts</h2>
    @if(isset($banks) && $banks->count())
        <div class="overflow-x-auto">
    <table class="min-w-full text-sm rounded-xl shadow border border-gray-200 overflow-hidden">
        <thead>
            <tr class="bg-blue-50 border-b border-gray-200">
                <th class="px-6 py-3 text-left font-bold text-gray-700">Bank Name</th>
                <th class="px-6 py-3 text-left font-bold text-gray-700">Account Title</th>
                <th class="px-6 py-3 text-left font-bold text-gray-700">Account Number</th>
                <th class="px-6 py-3 text-right font-bold text-gray-700">Opening Balance</th>
<th class="px-6 py-3 text-center font-bold text-gray-700">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($banks as $bank)
                <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-blue-100 transition">
                    <td class="px-6 py-3 border-b border-gray-100">{{ $bank->name }}</td>
                    <td class="px-6 py-3 border-b border-gray-100">{{ $bank->account_title }}</td>
                    <td class="px-6 py-3 border-b border-gray-100">{{ $bank->account_number }}</td>
                    <td class="px-6 py-3 border-b border-gray-100 text-right">{{ number_format($bank->opening_balance ?? 0, 2) }}</td>
<td class="px-6 py-3 border-b border-gray-100 text-center">
    <div class="flex items-center justify-center space-x-2">
        <a href="{{ route('banks.ledger', $bank->id) }}" class="inline-block px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-full text-xs font-semibold transition" title="Ledger">
            <svg xmlns="http://www.w3.org/2000/svg" class="inline w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4h9" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
            Ledger
        </a>
        <a href="{{ route('banks.edit', $bank->id) }}"
           class="inline-block px-3 py-1 bg-yellow-400 hover:bg-yellow-500 text-white rounded text-xs font-semibold transition"
           title="Edit">
            Edit
        </a>
    </div>
</td>
</tr>
            @endforeach
        </tbody>
    </table>
</div>
    @else
        <div class="text-gray-400 text-center py-12">
            No bank accounts found.
        </div>
    @endif
</div>
</div>
@endsection
