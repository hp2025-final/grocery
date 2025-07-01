@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Permission Management Form with Alpine.js -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" 
             x-data="permissionManager()" 
             x-init="init()">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-bold mb-4">User Permission Management (Alpine.js)</h2>

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

                <form method="POST" action="{{ route('admin.permissions.store') }}" class="space-y-6" @submit="handleSubmit">
                    @csrf
                    
                    <!-- Debug Information -->
                    @if(config('app.debug'))
                    <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                        <h4 class="font-medium text-yellow-800">Debug Info (Alpine.js):</h4>
                        <div class="text-sm text-yellow-700 mt-2">
                            <div>Selected User: <span x-text="selectedUser || 'None'"></span></div>
                            <div>Total Permissions Available: <span x-text="Object.keys(availablePermissions).length"></span></div>
                            <div>User's Current Permissions: <span x-text="userPermissions.length"></span></div>
                            <div>Checked Permissions: <span x-text="Object.values(checkedPermissions).filter(Boolean).length"></span></div>
                        </div>
                    </div>
                    @endif
                    
                    <div>
                        <label for="user" class="block text-sm font-medium text-gray-700">Select User</label>
                        <select name="user" 
                                x-model="selectedUser" 
                                @change="loadUserPermissions()" 
                                required 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select a user</option>
                            @foreach($users as $user)
                                <option value="{{ $user->email }}">
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-3 mb-6" x-show="selectedUser">
                        <button type="button" 
                                @click="selectAll()" 
                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm">
                            ✓ Select All
                        </button>
                        <button type="button" 
                                @click="clearAll()" 
                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm">
                            ✗ Clear All
                        </button>
                        <button type="button" 
                                @click="toggleByModule('Receivables')" 
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm">
                            Toggle Receivables
                        </button>
                        <button type="button" 
                                @click="toggleByModule('Payables')" 
                                class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded text-sm">
                            Toggle Payables
                        </button>
                    </div>

                    <!-- Permission Groups -->
                    <div x-show="selectedUser" class="space-y-6">
                        @foreach($routeGroups as $module => $sections)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                    <input type="checkbox" 
                                           :checked="isModuleFullyChecked('{{ $module }}')"
                                           @change="toggleModule('{{ $module }}')"
                                           class="mr-2 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    {{ $module }}
                                    <span class="ml-2 text-sm text-gray-500" 
                                          x-text="'(' + getModuleCheckedCount('{{ $module }}') + '/' + getModuleTotalCount('{{ $module }}') + ')'"></span>
                                </h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($sections as $sectionName => $permissions)
                                        <div class="bg-gray-50 p-3 rounded">
                                            <h4 class="font-medium text-gray-700 mb-2">{{ $sectionName }}</h4>
                                            <div class="space-y-1">
                                                @foreach($permissions as $permission => $label)
                                                    <label class="flex items-center text-sm">
                                                        <input type="checkbox" 
                                                               name="permissions[]" 
                                                               value="{{ $permission }}"
                                                               x-model="checkedPermissions['{{ $permission }}']"
                                                               class="mr-2 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                        <span class="text-gray-600">{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Submit Button -->
                    <div x-show="selectedUser" class="pt-6">
                        <button type="submit" 
                                :disabled="saving"
                                class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-bold py-2 px-4 rounded">
                            <span x-show="!saving">Update Permissions</span>
                            <span x-show="saving">Saving...</span>
                        </button>
                        <p class="text-sm text-gray-500 mt-2">
                            <span x-text="Object.values(checkedPermissions).filter(Boolean).length"></span> permissions will be saved for this user.
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <!-- Current User Permissions Summary -->
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

<script>
function permissionManager() {
    return {
        selectedUser: '',
        userPermissions: [],
        checkedPermissions: {},
        availablePermissions: @json($routeGroups),
        allUsersPermissions: @json($allPermissions),
        saving: false,

        init() {
            // Initialize available permissions
            this.initializeAvailablePermissions();
            
            // Pre-select user if there's one in session or URL
            const urlParams = new URLSearchParams(window.location.search);
            const userFromUrl = urlParams.get('user');
            const userFromSession = @json(session('selected_user'));
            
            if (userFromSession) {
                this.selectedUser = userFromSession;
                this.loadUserPermissions();
            } else if (userFromUrl) {
                this.selectedUser = userFromUrl;
                this.loadUserPermissions();
            }
            
            console.log('Permission Manager initialized with Alpine.js');
        },

        initializeAvailablePermissions() {
            // Flatten all available permissions
            for (const module in this.availablePermissions) {
                for (const section in this.availablePermissions[module]) {
                    for (const permission in this.availablePermissions[module][section]) {
                        this.checkedPermissions[permission] = false;
                    }
                }
            }
        },

        async loadUserPermissions() {
            if (!this.selectedUser) {
                this.userPermissions = [];
                this.resetAllPermissions();
                return;
            }

            try {
                // First check if we have cached permissions
                if (this.allUsersPermissions[this.selectedUser]) {
                    this.userPermissions = this.allUsersPermissions[this.selectedUser].permissions || [];
                } else {
                    // Fetch from server
                    const response = await fetch(`/admin/permissions/${encodeURIComponent(this.selectedUser)}`);
                    const data = await response.json();
                    this.userPermissions = data.permissions || [];
                    
                    // Update cache
                    this.allUsersPermissions[this.selectedUser] = { permissions: this.userPermissions };
                }

                // Reset all checkboxes
                this.resetAllPermissions();

                // Set user's permissions as checked
                this.userPermissions.forEach(permission => {
                    if (this.checkedPermissions.hasOwnProperty(permission)) {
                        this.checkedPermissions[permission] = true;
                    }
                });

                console.log('Loaded permissions for user:', this.selectedUser, this.userPermissions);
            } catch (error) {
                console.error('Error loading user permissions:', error);
                this.userPermissions = [];
                this.resetAllPermissions();
            }
        },

        resetAllPermissions() {
            for (const permission in this.checkedPermissions) {
                this.checkedPermissions[permission] = false;
            }
        },

        selectAll() {
            for (const permission in this.checkedPermissions) {
                this.checkedPermissions[permission] = true;
            }
        },

        clearAll() {
            if (confirm('Are you sure you want to clear all permissions?')) {
                this.resetAllPermissions();
            }
        },

        toggleModule(moduleName) {
            const modulePermissions = this.getModulePermissions(moduleName);
            const isFullyChecked = this.isModuleFullyChecked(moduleName);
            
            modulePermissions.forEach(permission => {
                this.checkedPermissions[permission] = !isFullyChecked;
            });
        },

        toggleByModule(moduleName) {
            this.toggleModule(moduleName);
        },

        getModulePermissions(moduleName) {
            const permissions = [];
            if (this.availablePermissions[moduleName]) {
                for (const section in this.availablePermissions[moduleName]) {
                    for (const permission in this.availablePermissions[moduleName][section]) {
                        permissions.push(permission);
                    }
                }
            }
            return permissions;
        },

        isModuleFullyChecked(moduleName) {
            const modulePermissions = this.getModulePermissions(moduleName);
            return modulePermissions.length > 0 && modulePermissions.every(permission => this.checkedPermissions[permission]);
        },

        getModuleCheckedCount(moduleName) {
            const modulePermissions = this.getModulePermissions(moduleName);
            return modulePermissions.filter(permission => this.checkedPermissions[permission]).length;
        },

        getModuleTotalCount(moduleName) {
            return this.getModulePermissions(moduleName).length;
        },

        handleSubmit(event) {
            if (!this.selectedUser) {
                event.preventDefault();
                alert('Please select a user first.');
                return;
            }

            this.saving = true;
            
            // Create hidden inputs for all permissions (checked and unchecked)
            const form = event.target;
            
            // Remove any existing hidden inputs
            form.querySelectorAll('input[name="all_permissions[]"]').forEach(input => input.remove());
            
            // Add hidden inputs for all permissions with their state
            for (const permission in this.checkedPermissions) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'all_permissions[]';
                hiddenInput.value = permission + '|' + (this.checkedPermissions[permission] ? '1' : '0');
                form.appendChild(hiddenInput);
            }

            const checkedCount = Object.values(this.checkedPermissions).filter(Boolean).length;
            console.log('Submitting permissions for user:', this.selectedUser);
            console.log('Total checked permissions:', checkedCount);
            console.log('All permissions:', this.checkedPermissions);

            // Re-enable saving after a delay in case of error
            setTimeout(() => {
                this.saving = false;
            }, 5000);
        }
    }
}
</script>
@endsection
