@extends('layouts.app')
@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Admin Dashboard</h1>
                    <div class="text-right">
                        <x-admin-company-info />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">                    <a href="{{ route('admin.sales-form-copy') }}" class="block p-6 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 hover:border-gray-300 transition">
                        <h5 class="mb-2 text-lg font-bold tracking-tight text-gray-900">Sales Form Copy</h5>
                        <p class="font-normal text-gray-700">View the copy of the sales form interface.</p>
                    </a>
                    <a href="{{ route('admin.purchase-form-copy') }}" class="block p-6 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 hover:border-gray-300 transition">
                        <h5 class="mb-2 text-lg font-bold tracking-tight text-gray-900">Purchase Form Copy</h5>
                        <p class="font-normal text-gray-700">View the copy of the purchase form interface.</p>
                    </a>
                    <!-- Add more admin links here as needed -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
