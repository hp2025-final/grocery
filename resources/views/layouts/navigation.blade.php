<nav class="flex flex-col h-full" x-data="{ activeSection: 'receivables' }">
    <!-- Menu -->
    <ul class="space-y-2 flex-1">
        <li>
            <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2 rounded hover:bg-gray-100 text-xs {{ request()->routeIs('dashboard') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="font-bold">Dashboard</span>
            </a>
        </li>

        <!-- Receivable Forms Section -->
        <li class="mt-2">
            <button @click="activeSection = activeSection === 'receivables' ? null : 'receivables'" class="flex items-center w-full px-3 py-2 rounded hover:bg-gray-100 font-medium text-gray-700 focus:outline-none focus:bg-gray-100">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="uppercase text-xs text-gray-400 font-bold tracking-wider flex-1 text-left">Receivable Forms</span>
                <svg :class="{'transform rotate-90': activeSection === 'receivables'}" class="h-4 w-4 ml-2 text-gray-500 transition-transform duration-200" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l6 6-6 6" />
                </svg>
            </button>
            <ul class="space-y-1 mt-1 pl-3 border-l border-gray-100" x-show="activeSection === 'receivables'" x-transition>
                <li>
                    <a href="{{ route('sales.create') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('sales.create') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        Add Sale
                    </a>
                </li>
                <li>
                    <a href="{{ route('customer-receipts.create') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('customer-receipts.create') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        Customer Receipt
                    </a>
                </li>
                <li>
                    <a href="{{ route('customers.create') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('customers.create') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        Add Customer
                    </a>
                </li>
                <li>
                    <a href="{{ route('customer-balances.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('customer-balances.index') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        View Balances
                    </a>
                </li>
            </ul>
        </li>

        <!-- Payable Forms Section -->
        <li class="mt-2">
            <button @click="activeSection = activeSection === 'payables' ? null : 'payables'" class="flex items-center w-full px-3 py-2 rounded hover:bg-gray-100 font-medium text-gray-700 focus:outline-none focus:bg-gray-100">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span class="uppercase text-xs text-gray-400 font-bold tracking-wider flex-1 text-left">Payable Forms</span>
                <svg :class="{'transform rotate-90': activeSection === 'payables'}" class="h-4 w-4 ml-2 text-gray-500 transition-transform duration-200" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l6 6-6 6" />
                </svg>
            </button>
            <ul class="space-y-1 mt-1 pl-3 border-l border-gray-100" x-show="activeSection === 'payables'" x-transition>
                <li>
                    <a href="{{ route('purchases.create') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('purchases.create') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        Add Purchase
                    </a>
                </li>
                <li>
                    <a href="{{ route('vendor-payments.create') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('vendor-payments.create') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        Vendor Payment
                    </a>
                </li>
                <li>
                    <a href="{{ route('vendors.create') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('vendors.create') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        Add Vendor
                    </a>
                </li>
                <li>
                    <a href="{{ route('vendor-balances.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('vendor-balances.index') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        View Balances
                    </a>
                </li>
            </ul>
        </li>

        <!-- Inventory Forms Section -->
        <li class="mt-2">
            <button @click="activeSection = activeSection === 'inventory' ? null : 'inventory'" class="flex items-center w-full px-3 py-2 rounded hover:bg-gray-100 font-medium text-gray-700 focus:outline-none focus:bg-gray-100">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span class="uppercase text-xs text-gray-400 font-bold tracking-wider flex-1 text-left">Inventory Forms</span>
                <svg :class="{'transform rotate-90': activeSection === 'inventory'}" class="h-4 w-4 ml-2 text-gray-500 transition-transform duration-200" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l6 6-6 6" />
                </svg>
            </button>
            <ul class="space-y-1 mt-1 pl-3 border-l border-gray-100" x-show="activeSection === 'inventory'" x-transition>
                <li>
                    <a href="{{ route('inventory-categories.create') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('inventory-categories.create') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        Add Inventory Category
                    </a>
                </li>
                <li>
                    <a href="{{ route('inventory.create') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('inventory.create') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        Add Product / Inventory
                    </a>
                </li>
                <li>
                    <a href="{{ route('inventory-balances.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('inventory-balances.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        Inventory Balances
                    </a>
                </li>
                <li>
                    <a href="{{ route('inventory-values.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('inventory-values.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        Inventory Values
                    </a>
                </li>
                <li>
                    <a href="{{ route('stock-adjustments.create') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('stock-adjustments.create') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        Stock Adjustment
                    </a>
                </li>
            </ul>
        </li>

        <!-- Bank Forms Section -->
        <li class="mt-2">
            <button @click="activeSection = activeSection === 'bank' ? null : 'bank'" class="flex items-center w-full px-3 py-2 rounded hover:bg-gray-100 font-medium text-gray-700 focus:outline-none focus:bg-gray-100">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l9-4 9 4v12l-9 4-9-4V6z M12 3v18 M3 6l9 4 9-4" />
                </svg>
                <span class="uppercase text-xs text-gray-400 font-bold tracking-wider flex-1 text-left">Bank Forms</span>
                <svg :class="{'transform rotate-90': activeSection === 'bank'}" class="h-4 w-4 ml-2 text-gray-500 transition-transform duration-200" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l6 6-6 6" />
                </svg>
            </button>
            <ul class="space-y-1 mt-1 pl-3 border-l border-gray-100" x-show="activeSection === 'bank'" x-transition>
                <li>
                    <a href="{{ route('banks.create') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('banks.create') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        Add Bank
                    </a>
                </li>
                <li>
                    <a href="{{ route('bank_transfers.create') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('bank_transfers.create') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        Internal Bank Transfer
                    </a>
                </li>
                <li>
                    <a href="{{ route('bank-balances.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('bank-balances.index') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        View Balances
                    </a>
                </li>
            </ul>
        </li>

        <!-- Expense Forms Section -->
        <li class="mt-2">
            <button @click="activeSection = activeSection === 'expense' ? null : 'expense'" class="flex items-center w-full px-3 py-2 rounded hover:bg-gray-100 font-medium text-gray-700 focus:outline-none focus:bg-gray-100">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
                </svg>
                <span class="uppercase text-xs text-gray-400 font-bold tracking-wider flex-1 text-left">Expense Forms</span>
                <svg :class="{'transform rotate-90': activeSection === 'expense'}" class="h-4 w-4 ml-2 text-gray-500 transition-transform duration-200" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l6 6-6 6" />
                </svg>
            </button>
            <ul class="space-y-1 mt-1 pl-3 border-l border-gray-100" x-show="activeSection === 'expense'" x-transition>
                <li>
                    <a href="{{ route('expenses.create') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('expenses.create') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        Add Expense
                    </a>
                </li>
                <li>
                    <a href="{{ route('expense-accounts.create') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('expense-accounts.create') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        Add Expense Account
                    </a>
                </li>
            </ul>
        </li>

        <!-- Reports Section -->
        <li class="mt-4">
            <button @click="activeSection = activeSection === 'reports' ? null : 'reports'" class="flex items-center w-full px-3 py-2 rounded hover:bg-gray-100 font-medium text-gray-700 focus:outline-none focus:bg-gray-100">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="uppercase text-xs text-gray-400 font-bold tracking-wider flex-1 text-left">Reports</span>
                <svg :class="{'transform rotate-90': activeSection === 'reports'}" class="h-4 w-4 ml-2 text-gray-500 transition-transform duration-200" fill="none" viewBox="0 0 20 20" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l6 6-6 6" />
                </svg>
            </button>
            <ul class="space-y-1 mt-1 pl-3 border-l border-gray-100" x-show="activeSection === 'reports'" x-transition>
                <li>
                    <a href="{{ route('reports.trial_balance') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('reports.trial_balance') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        Trial Balance
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports.general_ledger') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('reports.general_ledger') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        General Ledger
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports.journal') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('reports.journal') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        Journal Report
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports.income_statement') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('reports.income_statement') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        Income Statement
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports.balance_sheet') }}" class="block px-3 py-2 rounded hover:bg-gray-100 font-medium text-xs {{ request()->routeIs('reports.balance_sheet') ? 'bg-gray-100 text-gray-900' : 'text-gray-700' }}">
                        Balance Sheet
                    </a>
                </li>


            </ul>
        </li>
    </ul>

    <!-- User info and logout -->
    <div class="mt-auto pt-8 pb-4 px-3 border-t">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full text-left px-3 py-2 rounded hover:bg-red-50 text-red-600 font-medium text-xs flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Sign Out
            </button>
        </form>
    </div>
</nav>
