<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.company.name') }}</title>
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

            <span class="text-xl font-bold text-gray-900">{{ config('app.company.name') }}</span>
        </div>
        <div class="flex flex-1 h-[calc(100vh-3.5rem)] overflow-hidden">
            <!-- Sidebar (desktop) -->
            <aside class="hidden md:flex flex-col w-56 bg-white border-r border-gray-100 min-h-screen sticky top-14 overflow-y-auto">
                @include('layouts.navigation')
            </aside>
            <!-- Sidebar overlay (mobile) -->
            <div x-show="sidebarOpen" class="fixed inset-0 z-40 flex md:hidden" style="display: none;">
                <div class="fixed inset-0 bg-black opacity-30" @click="sidebarOpen = false"></div>
                <aside class="relative w-[280px] max-w-[90vw] bg-white h-screen shadow-xl overflow-y-auto">
                    <div class="sticky top-0 bg-white z-10 px-4 py-3 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-semibold text-gray-900">Menu</span>
                            <button @click="sidebarOpen = false" class="p-2 rounded-full hover:bg-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
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
