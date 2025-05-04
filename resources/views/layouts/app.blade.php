<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Steh Enterprise</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Optionally include Breeze's CSS/JS if needed -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 font-sans antialiased h-full">
    <div x-data="{ sidebarOpen: false }" class="min-h-full flex flex-col">
        <!-- Topbar -->
        <div class="w-full h-14 bg-white border-b flex items-center px-4 shadow-sm z-20 sticky top-0">
            <!-- Hamburger (mobile only) -->
            <button @click="sidebarOpen = true" class="md:hidden mr-3 p-2 rounded hover:bg-gray-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <span class="text-xl font-bold text-gray-900">Steh Enterprise</span>
            <div class="flex-1"></div>
            <!-- User dropdown (top right) -->
            <div x-data="{ open: false }" class="relative">

                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-44 bg-white border border-gray-100 rounded shadow-lg z-50 py-1">
                    <a href="{{ route('banks.index') }}" class="block px-4 py-2 hover:bg-gray-100 rounded">Add Bank</a>
                    <a href="{{ route('bank_transfers.create') }}" class="block px-4 py-2 hover:bg-gray-100 rounded">Internal Bank Transfer</a>
                    <a href="{{ route('expenses.index') }}" class="block px-4 py-2 hover:bg-gray-100 rounded">Add Expense</a>
                    <a href="{{ route('expense_accounts.index') }}" class="block px-4 py-2 hover:bg-gray-100 rounded">Add Expense Account</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 text-left text-gray-900 hover:bg-gray-100">Sign Out</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="flex flex-1 h-[calc(100vh-3.5rem)] overflow-hidden">
            <!-- Sidebar (desktop) -->
            <aside class="hidden md:flex flex-col w-56 bg-white border-r border-gray-100 h-full overflow-y-auto">
                @include('layouts.navigation')
            </aside>
            <!-- Sidebar overlay (mobile) -->
            <div x-show="sidebarOpen" class="fixed inset-0 z-40 flex md:hidden" style="display: none;">
                <div class="fixed inset-0 bg-black opacity-30" @click="sidebarOpen = false"></div>
                <aside class="relative w-[280px] max-w-[90vw] bg-white h-full shadow-xl overflow-y-auto mt-14">
                    <div class="flex items-center justify-end mb-8">
    <button @click="sidebarOpen = false" class="p-2 rounded hover:bg-gray-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>
                    @include('layouts.navigation')
                </aside>
            </div>
            <!-- Main content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
