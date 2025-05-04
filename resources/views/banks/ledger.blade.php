@extends('layouts.app')
@section('content')
<div class="max-w-5xl mx-auto py-8 px-2">
    <h1 class="text-2xl font-bold mb-6">Bank Ledger: {{ $bank->name }}</h1>

    <div class="flex justify-between items-end mb-4">
        <form method="get" class="flex flex-wrap gap-2 items-end">
            <div>
                <label class="block text-xs font-semibold mb-1">From</label>
                <input type="date" name="from" value="{{ $from }}" class="form-input rounded border-gray-300" />
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1">To</label>
                <input type="date" name="to" value="{{ $to }}" class="form-input rounded border-gray-300" />
            </div>
            <button type="submit" class="ml-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Filter</button>
        </form>

        <a href="{{ route('banks.ledger.export', ['bank' => $bank->id, 'from' => $from, 'to' => $to]) }}" 
           class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export PDF
        </a>
    </div>

    <div class="bg-white rounded-xl shadow overflow-x-auto">
        @include('banks._ledger_table', ['entries' => $entries, 'openingBalance' => $openingBalance ?? null, 'from' => $from, 'bank' => $bank])
    </div>
</div>
@endsection
