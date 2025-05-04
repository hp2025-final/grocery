@extends('layouts.app')
@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="flex flex-col md:flex-row md:space-x-6 max-w-7xl mx-auto py-8 px-2">
    <!-- Section A: Expense Account Form -->
    <div class="bg-white rounded-xl shadow p-8 w-full md:w-2/5 mb-8 md:mb-0">
        @if(session('success'))
            <div class="mb-4 rounded bg-green-100 border border-green-200 text-green-700 px-4 py-3 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        <h2 class="text-2xl font-bold mb-6">Add Expense Account</h2>
        <form method="POST" action="{{ route('expense-accounts.store') }}" class="space-y-0">
            @csrf
            <div class="mb-4">
                <label class="block font-medium mb-1">Account Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" class="w-full border rounded px-3 py-2" required value="{{ old('name') }}">
            </div>
            <div class="text-right md:text-right">
                <button type="submit" class="px-8 py-2 w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded transition">Create Expense Account</button>
            </div>
        </form>
    </div>
    <!-- Section B: Expense Account List -->
    <div class="bg-white rounded-xl shadow p-8 w-full md:w-3/5">
        <h2 class="text-xl font-bold mb-4">Expense Accounts</h2>
        @if(isset($expenseAccounts) && $expenseAccounts->count())
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm rounded-xl shadow border border-gray-200 overflow-hidden">
                    <thead>
                        <tr class="bg-blue-50 border-b border-gray-200">
                            <th class="px-6 py-3 text-left font-bold text-gray-700">Name</th>
                            <th class="px-6 py-3 text-left font-bold text-gray-700">Description</th>
                            <th class="px-6 py-3 text-right font-bold text-gray-700">Opening Balance</th>
                            <th class="px-6 py-3 text-left font-bold text-gray-700">Created At</th>
                            <th class="px-6 py-3 text-center font-bold text-gray-700">Ledger</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenseAccounts as $account)
                            <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-blue-100 transition">
                                <td class="px-6 py-3 border-b border-gray-100">{{ $account->name }}</td>
                                <td class="px-6 py-3 border-b border-gray-100">{{ $account->description }}</td>
                                <td class="px-6 py-3 border-b border-gray-100 text-right">{{ number_format($account->opening_balance ?? 0, 2) }}</td>
                                <td class="px-6 py-3 border-b border-gray-100">{{ $account->created_at ? $account->created_at->format('Y-m-d') : '' }}</td>
                                <td class="px-6 py-3 border-b border-gray-100 text-center">
                                    <a href="{{ route('accounts.ledger', ['id' => $account->id]) }}" class="inline-block px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-full text-xs font-semibold transition" title="Ledger">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="inline w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4h9" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                                        Ledger
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-gray-400 text-center py-12">
                No expense accounts found.
            </div>
        @endif
    </div>
</div>
@endsection
