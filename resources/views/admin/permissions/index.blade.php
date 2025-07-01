@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Permission Management Form -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-bold mb-4">User Permission Management</h2>

                @if(session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                @if(session('message'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('message') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.permissions.store') }}" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label for="user" class="block text-sm font-medium text-gray-700">Select User</label>
                        <select name="user" id="user" required 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select a user</option>
                            @foreach($users as $user)
                                <option value="{{ $user->email }}">
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium text-gray-700 mb-3">Module Permissions</h3>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            @foreach($routeGroups as $moduleName => $sections)
                                <div class="bg-gray-50 p-4 rounded-md">
                                    <h4 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                        {{ $moduleName }}
                                    </h4>
                                    <div class="space-y-6">
                                        @foreach($sections as $sectionName => $permissions)
                                            <div class="ml-4">
                                                <h5 class="font-medium text-gray-800 mb-2 text-sm uppercase tracking-wider">{{ $sectionName }}</h5>
                                                <div class="space-y-2 ml-4">
                                                    @foreach($permissions as $route => $description)
                                                        <div class="flex items-center">
                                                            <input type="checkbox" name="permissions[]" 
                                                                   value="{{ $route }}" 
                                                                   id="permission_{{ $route }}"
                                                                   class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                            <label for="permission_{{ $route }}" 
                                                                   class="ml-2 block text-sm text-gray-700">
                                                                {{ $description }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                                id="saveButton">
                            Save Permissions
                        </button>
                        <button type="button" 
                                class="ml-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                onclick="clearAllPermissions()">
                            Clear All
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- User Permissions Summary -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h3 class="text-xl font-bold mb-4">Current User Permissions</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modules Access</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Permissions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($users as $user)
                                @php
                                    $userPermissions = $allPermissions[$user->email]['permissions'] ?? [];
                                    $modules = collect($userPermissions)->map(function($permission) use ($routeGroups) {
                                        foreach ($routeGroups as $module => $sections) {
                                            foreach ($sections as $sectionPerms) {
                                                if (array_key_exists($permission, $sectionPerms)) {
                                                    return $module;
                                                }
                                            }
                                        }
                                        return null;
                                    })->filter()->unique();
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $user->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($modules as $module)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $module }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ count($userPermissions) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const userSelect = document.getElementById('user');
    const allPermissions = @json($allPermissions);
    
    function loadUserPermissions(email) {
        if (!email) {
            // Clear all checkboxes
            document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = false);
            return;
        }

        // Get the user's current permissions
        const userPermissions = allPermissions[email]?.permissions || [];
        
        console.log('Loading permissions for:', email, userPermissions); // Debug log

        // Clear all checkboxes first
        document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = false);

        // Set the checkboxes based on user's permissions
        userPermissions.forEach(permission => {
            const checkbox = document.getElementById(`permission_${permission}`);
            if (checkbox) {
                checkbox.checked = true;
                console.log('Checked permission:', permission); // Debug log
            } else {
                console.log('Checkbox not found for permission:', permission); // Debug log
            }
        });
    }

    // Handle user selection change
    userSelect.addEventListener('change', function() {
        const email = this.value;
        loadUserPermissions(email);
    });

    // Pre-select user if there's one in the URL
    const urlParams = new URLSearchParams(window.location.search);
    const userEmail = urlParams.get('user');
    if (userEmail) {
        userSelect.value = userEmail;
        loadUserPermissions(userEmail);
    }

    // Function to clear all permissions
    function clearAllPermissions() {
        if (confirm('Are you sure you want to clear all permissions for the selected user?')) {
            document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = false);
        }
    }

    // Form submission validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const selectedUser = userSelect.value;
        if (!selectedUser) {
            e.preventDefault();
            alert('Please select a user first.');
            return false;
        }

        const checkedPermissions = document.querySelectorAll('input[name="permissions[]"]:checked');
        console.log('Submitting permissions:', Array.from(checkedPermissions).map(cb => cb.value)); // Debug log
        
        // Show loading state
        const submitButton = document.getElementById('saveButton');
        const originalText = submitButton.textContent;
        submitButton.textContent = 'Saving...';
        submitButton.disabled = true;
        
        // Re-enable button after a short delay (form submission will redirect anyway)
        setTimeout(() => {
            submitButton.textContent = originalText;
            submitButton.disabled = false;
        }, 3000);
    });

    // If there's a success message, reload the permissions data
    @if(session('message'))
        // Get the selected user from the form  
        setTimeout(() => {
            // Refresh the current permissions display
            const currentUser = userSelect.value;
            if (currentUser) {
                loadUserPermissions(currentUser);
            }
        }, 1000); // Wait 1 second for success message to show
    @endif
});
</script>
@endpush
@endsection
