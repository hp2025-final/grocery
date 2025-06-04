@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-2 py-4 sm:py-6">
    <div class="bg-white rounded-lg shadow">
        <div class="p-4">
            <h2 class="text-lg font-semibold mb-4">Bank Balances</h2>

            <form action="{{ route('bank-balances.index') }}" method="GET" class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">From:</label>
                    <input type="date" name="from" value="{{ $from }}" 
                           class="w-full border-gray-300 rounded text-sm px-2 py-1">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">To:</label>
                    <input type="date" name="to" value="{{ $to }}" 
                           class="w-full border-gray-300 rounded text-sm px-2 py-1">
                </div>
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full bg-blue-600 text-white px-3 py-1.5 rounded text-sm hover:bg-blue-700">
                        Filter
                    </button>
                </div>
                <div class="flex items-end">
                    <a href="{{ route('bank-balances.export', ['from' => request('from'), 'to' => request('to')]) }}" 
                       class="w-full bg-green-500 text-white px-3 py-1.5 rounded text-sm text-center hover:bg-green-600">
                        Export PDF
                    </a>
                </div>
            </form>

            <!-- Mobile View (Cards) -->
            <div class="block sm:hidden space-y-2">
                @foreach($banks as $bank)
                <div class="bg-white border rounded-lg p-3 shadow-sm">
                    <div class="flex justify-between items-start mb-2">
                        <div class="font-medium text-gray-900">{{ $bank->name }}</div>
                        <a href="{{ url('/banks/' . $bank->id . '/ledger') }}" 
                           class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-500 hover:bg-blue-600 text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <div class="text-gray-500">Opening</div>
                            <div class="font-medium">{{ number_format($bank->opening_balance, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Receipts</div>
                            <div class="font-medium">{{ number_format($bank->current_month_receipts, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Payments</div>
                            <div class="font-medium">{{ number_format($bank->current_month_payments, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Balance</div>
                            <div class="font-medium">{{ number_format($bank->closing_balance, 2) }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
                <!-- Mobile Total Card -->
                <div class="bg-gray-50 border rounded-lg p-3 shadow-sm">
                    <div class="text-sm font-medium text-gray-900 mb-2">Total</div>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <div class="text-gray-500">Opening</div>
                            <div class="font-medium">{{ number_format($banks->sum('opening_balance'), 2) }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Receipts</div>
                            <div class="font-medium">{{ number_format($banks->sum('current_month_receipts'), 2) }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Payments</div>
                            <div class="font-medium">{{ number_format($banks->sum('current_month_payments'), 2) }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Balance</div>
                            <div class="font-medium">{{ number_format($banks->sum('closing_balance'), 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desktop View (Table) -->
            <div class="hidden sm:block">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs sm:text-[12px] font-medium text-gray-500 uppercase tracking-wider">Bank Name</th>
                                <th class="px-3 py-2 text-right text-xs sm:text-[12px] font-medium text-gray-500 uppercase tracking-wider">Opening Balance</th>
                                <th class="px-3 py-2 text-right text-xs sm:text-[12px] font-medium text-gray-500 uppercase tracking-wider">Receipts</th>
                                <th class="px-3 py-2 text-right text-xs sm:text-[12px] font-medium text-gray-500 uppercase tracking-wider">Payments</th>
                                <th class="px-3 py-2 text-right text-xs sm:text-[12px] font-medium text-gray-500 uppercase tracking-wider">Closing Balance</th>
                                <th class="px-3 py-2 text-center text-xs sm:text-[12px] font-medium text-gray-500 uppercase tracking-wider">View</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($banks as $bank)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-sm sm:text-[12px] text-gray-900">{{ $bank->name }}</td>
                                <td class="px-3 py-2 text-sm sm:text-[12px] text-gray-900 text-right">{{ number_format($bank->opening_balance, 2) }}</td>
                                <td class="px-3 py-2 text-sm sm:text-[12px] text-gray-900 text-right">{{ number_format($bank->current_month_receipts, 2) }}</td>
                                <td class="px-3 py-2 text-sm sm:text-[12px] text-gray-900 text-right">{{ number_format($bank->current_month_payments, 2) }}</td>
                                <td class="px-3 py-2 text-sm sm:text-[12px] text-gray-900 text-right">{{ number_format($bank->closing_balance, 2) }}</td>
                                <td class="px-3 py-2 text-center">
                                    <a href="{{ url('/banks/' . $bank->id . '/ledger') }}" class="inline-block px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-full text-xs font-semibold transition" title="Ledger">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="inline w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4h9" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                                        Ledger
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td class="px-3 py-2 text-sm sm:text-[12px] font-medium text-gray-900">Total</td>
                                <td class="px-3 py-2 text-sm sm:text-[12px] font-medium text-gray-900 text-right">{{ number_format($banks->sum('opening_balance'), 2) }}</td>
                                <td class="px-3 py-2 text-sm sm:text-[12px] font-medium text-gray-900 text-right">{{ number_format($banks->sum('current_month_receipts'), 2) }}</td>
                                <td class="px-3 py-2 text-sm sm:text-[12px] font-medium text-gray-900 text-right">{{ number_format($banks->sum('current_month_payments'), 2) }}</td>
                                <td class="px-3 py-2 text-sm sm:text-[12px] font-medium text-gray-900 text-right">{{ number_format($banks->sum('closing_balance'), 2) }}</td>
                                <td class="px-3 py-2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection