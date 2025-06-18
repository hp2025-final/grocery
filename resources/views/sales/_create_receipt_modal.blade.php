@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-900">Create Receipt for Sale #{{ $sale->sale_number }}</h2>
                <a href="{{ route('sales.index') }}" class="text-gray-600 hover:text-gray-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            </div>

            @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Error!</strong>
                <ul class="mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded relative" role="alert">
                {{ session('error') }}
            </div>
            @endif
              <form action="{{ route('customer-receipts.store-from-sale') }}" method="POST">
                @csrf
                <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                <input type="hidden" name="payment_method" value="Bank">
                
                <div class="space-y-6">
                    <!-- Sale Details -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Customer:</p>
                                <p class="text-sm font-medium">{{ $sale->customer->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Amount to be Received:</p>
                                <p class="text-sm font-medium">Rs. {{ number_format($sale->net_amount, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Invoice Date:</p>
                                <p class="text-sm font-medium">{{ date('d-m-Y', strtotime($sale->sale_date)) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Status:</p>
                                <p class="text-sm font-medium">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ $sale->payment_status }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Bank Account -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account <span class="text-red-500">*</span></label>
                        <select name="payment_account_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('payment_account_id') border-red-500 @enderror" required>
                            <option value="">Select Bank Account</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->id }}" {{ old('payment_account_id') == $bank->id ? 'selected' : '' }}>
                                    {{ $bank->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_account_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="3" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('notes') border-red-500 @enderror"
                            placeholder="Add any notes about this receipt...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('sales.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Create Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
