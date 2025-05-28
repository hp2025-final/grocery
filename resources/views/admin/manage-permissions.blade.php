@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-bold mb-4">Manage User Permissions</h2>

                @if(session('message'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                        {{ session('message') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('permissions.store') }}">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="user" class="block text-sm font-medium text-gray-700">Select User</label>
                        <select name="user" id="user" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            @foreach($users as $user)
                                <option value="{{ $user->email }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Available Pages</h3>
                        <div class="space-y-2">
                            @foreach($routes as $route)
                                <div class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="{{ $route }}" 
                                           id="permission_{{ $route }}" class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                    <label for="permission_{{ $route }}" class="ml-2 block text-sm text-gray-900">
                                        {{ $route }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Save Permissions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
