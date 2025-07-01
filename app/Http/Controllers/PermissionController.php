<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class PermissionController extends Controller
{    
    private $moduleGroups = [
        'Receivables' => [
            'Sales Management' => [
                'sales.create' => 'Create New Sale Invoice',
                'sales.store' => 'Save Sale Invoice',
                'sales.index' => 'View All Sale Invoices',
                'sales.show' => 'View Single Sale Invoice',
                'sales.edit' => 'Edit Sale Invoice',
                'sales.update' => 'Update Sale Invoice',
                'sales.destroy' => 'Delete Sale Invoice',
                'sales.pdf' => 'Print Sale Invoice',
                'sales.export_pdf' => 'Export All Sales PDF'
            ],
            'Customer Management' => [
                'customers.index' => 'View All Customers',
                'customers.create' => 'Create New Customer',
                'customers.store' => 'Save Customer',
                'customers.edit' => 'Edit Customer',
                'customers.update' => 'Update Customer',
                'customers.destroy' => 'Delete Customer'
            ],
            'Customer Receipts' => [
                'customer-receipts.index' => 'View All Customer Receipts',
                'customer-receipts.create' => 'Create New Receipt',
                'customer-receipts.store' => 'Save Receipt',
                'customer-receipts.edit' => 'Edit Receipt',
                'customer-receipts.update' => 'Update Receipt',
                'customer-receipts.destroy' => 'Delete Receipt',
                'customer-receipts.live-search' => 'Search Receipts',
                'customer-receipts.export-pdf' => 'Export Receipt PDF',
                'customer-receipts.export-table' => 'Export Receipt Table',
                'customer-receipts.create-from-sale' => 'Create Receipt from Sale',
                'customer-receipts.store-from-sale' => 'Save Receipt from Sale',
                'receipts.index' => 'View All Receipts'
            ],
            'Customer Reports' => [
                'customers.ledger' => 'View Customer Ledger',
                'customers.ledger.export' => 'Export Customer Ledger PDF',
                'customer-balances.index' => 'View Customer Balances',
                'customer-balances.export' => 'Export Customer Balances PDF',
                'reports.customer_list' => 'View Customer List Report'
            ]
        ],
        'Payables' => [
            'Purchase Management' => [
                'purchases.create' => 'Create New Purchase Invoice',
                'purchases.store' => 'Save Purchase Invoice',
                'purchases.index' => 'View All Purchases',
                'purchases.show' => 'View Single Purchase',
                'purchases.edit' => 'Edit Purchase Invoice',
                'purchases.update' => 'Update Purchase Invoice',
                'purchases.destroy' => 'Delete Purchase',
                'purchases.pdf' => 'Print Purchase Invoice'
            ],
            'Vendor Management' => [
                'vendors.index' => 'View All Vendors',
                'vendors.create' => 'Create New Vendor',
                'vendors.store' => 'Save Vendor',
                'vendors.edit' => 'Edit Vendor',
                'vendors.update' => 'Update Vendor',
                'vendors.destroy' => 'Delete Vendor'
            ],
            'Vendor Payments' => [
                'vendor-payments.index' => 'View All Vendor Payments',
                'vendor-payments.create' => 'Create New Payment',
                'vendor-payments.store' => 'Save Payment',
                'vendor-payments.edit' => 'Edit Payment',
                'vendor-payments.update' => 'Update Payment',
                'vendor-payments.destroy' => 'Delete Payment',
                'vendor-payments.live-search' => 'Search Payments',
                'vendor-payments.export-pdf' => 'Export Payment PDF',
                'vendor-payments.export-table' => 'Export Payment Table',
                'vendor-payments.create-from-purchase' => 'Create Payment from Purchase',
                'vendor-payments.store-from-purchase' => 'Save Payment from Purchase'
            ],
            'Vendor Reports' => [
                'vendors.ledger' => 'View Vendor Ledger',
                'vendors.ledger.export' => 'Export Vendor Ledger PDF',
                'vendor-balances.index' => 'View Vendor Balances',
                'vendor-balances.export' => 'Export Vendor Balances PDF'
            ]
        ],
        'Inventory Management' => [
            'Product Management' => [
                'inventory.index' => 'View All Products',
                'inventory.create' => 'Create New Product',
                'inventory.store' => 'Save Product',
                'inventory.edit' => 'Edit Product',
                'inventory.update' => 'Update Product',
                'inventory.destroy' => 'Delete Product'
            ],
            'Product Categories' => [
                'inventory-categories.index' => 'View Product Categories',
                'inventory-categories.create' => 'Create New Category',
                'inventory-categories.store' => 'Save Category',
                'inventory-categories.edit' => 'Edit Category',
                'inventory-categories.update' => 'Update Category',
                'inventory-categories.destroy' => 'Delete Category'
            ],
            'Stock Adjustments' => [
                'stock-adjustments.index' => 'View All Stock Adjustments',
                'stock-adjustments.create' => 'Create New Adjustment',
                'stock-adjustments.store' => 'Save Adjustment',
                'stock-adjustments.edit' => 'Edit Adjustment',
                'stock-adjustments.update' => 'Update Adjustment',
                'stock-adjustments.destroy' => 'Delete Adjustment'
            ],
            'Inventory Ledger' => [
                'inventory.ledger' => 'View Inventory Ledger',
                'inventory.ledger.export' => 'Export Inventory Ledger PDF',
                'inventory.ledger.without_rate' => 'View Ledger Without Rate',
                'inventory.ledger.without_rate.export' => 'Export Ledger Without Rate PDF'
            ],
            'Inventory Reports' => [
                'inventory-balances.index' => 'View Stock Balances',
                'inventory-balances.export' => 'Export Stock Balances PDF',
                'inventory-balances.export-without-prices' => 'Export Stock Without Prices',
                'inventory-values.index' => 'View Stock Values',
                'inventory-values.export-pdf' => 'Export Stock Values PDF',
                'inventory-values.info' => 'Download Stock Information',
                'reports.inventory.by-category' => 'View Inventory by Category',
                'reports.inventory.product-list' => 'View Product List Report',
                'reports.inventory.product-list.export' => 'Export Product List PDF'
            ]
        ],
        'Banking & Cash Management' => [
            'Bank Account Management' => [
                'banks.index' => 'View All Bank Accounts',
                'banks.create' => 'Create New Bank Account',
                'banks.store' => 'Save Bank Account',
                'banks.edit' => 'Edit Bank Account',
                'banks.update' => 'Update Bank Account',
                'banks.destroy' => 'Delete Bank Account'
            ],
            'Bank Transfers' => [
                'bank_transfers.index' => 'View All Bank Transfers',
                'bank_transfers.create' => 'Create New Transfer',
                'bank_transfers.store' => 'Save Transfer',
                'bank_transfers.edit' => 'Edit Transfer',
                'bank_transfers.update' => 'Update Transfer',
                'bank_transfers.destroy' => 'Delete Transfer',
                'bank_transfers.live_search' => 'Search Bank Transfers'
            ],
            'Bank Reports' => [
                'banks.ledger' => 'View Bank Ledger',
                'banks.ledger.export' => 'Export Bank Ledger PDF',
                'bank-balances.index' => 'View Bank Balances',
                'bank-balances.export' => 'Export Bank Balances PDF'
            ]
        ],
        'Expense Management' => [
            'Expense Transactions' => [
                'expenses.index' => 'View All Expenses',
                'expenses.create' => 'Create New Expense',
                'expenses.store' => 'Save Expense',
                'expenses.edit' => 'Edit Expense',
                'expenses.update' => 'Update Expense',
                'expenses.destroy' => 'Delete Expense',
                'expenses.tableAjax' => 'Load Expenses Table Data'
            ],
            'Expense Account Setup' => [
                'expense_accounts.index' => 'View All Expense Accounts',
                'expense-accounts.create' => 'Create New Expense Account',
                'expense-accounts.store' => 'Save Expense Account',
                'expense-accounts.edit' => 'Edit Expense Account',
                'expense-accounts.update' => 'Update Expense Account',
                'expense-accounts.destroy' => 'Delete Expense Account'
            ],
            'Expense Reports' => [
                'accounts.ledger' => 'View Expense Account Ledger',
                'expense-accounts.ledger.filter' => 'Filter Expense Ledger',
                'expense-accounts.ledger.export' => 'Export Expense Ledger PDF'
            ]
        ],
        'Financial Reports' => [
            'Core Financial Reports' => [
                'reports.trial_balance' => 'View Trial Balance',
                'reports.trial_balance.export' => 'Export Trial Balance PDF',
                'reports.general_ledger' => 'View General Ledger',
                'reports.general_ledger.export' => 'Export General Ledger PDF',
                'reports.income_statement' => 'View Income Statement',
                'reports.income_statement.export' => 'Export Income Statement PDF',
                'reports.balance_sheet' => 'View Balance Sheet',
                'reports.balance_sheet.export' => 'Export Balance Sheet PDF'
            ],
            'Transaction Reports' => [
                'reports.journal' => 'View Journal Entries Report',
                'reports.journal.export' => 'Export Journal Report PDF',
                'reports.daily_book' => 'View Daily Book Report',
                'reports.daily_book.export' => 'Export Daily Book PDF'
            ],
            'Analysis Reports' => [
                'reports.cash_flow' => 'View Cash Flow Report',
                'reports.profit_loss' => 'View Profit & Loss Report',
                'reports.financial_summary' => 'View Financial Summary'
            ]
        ],
        'Chart of Accounts' => [
            'Account Management' => [
                'chart-accounts.index' => 'View Chart of Accounts',
                'chart-accounts.create' => 'Create New Account',
                'chart-accounts.store' => 'Save Account',
                'chart-accounts.edit' => 'Edit Account',
                'chart-accounts.update' => 'Update Account',
                'chart-accounts.destroy' => 'Delete Account',
                'chart-accounts.show' => 'View Account Details'
            ],
            'Account Reports' => [
                'chart-accounts.ledger' => 'View Account Ledger',
                'chart-accounts.ledger.export' => 'Export Account Ledger PDF',
                'chart-accounts.balance' => 'View Account Balance',
                'chart-accounts.export' => 'Export Chart of Accounts'
            ]
        ],
        'Administration' => [
            'System Settings' => [
                'admin.index' => 'Access Admin Dashboard',
                'admin.settings' => 'Manage System Settings',
                'admin.company-settings' => 'Manage Company Settings',
                'admin.sales-form-copy' => 'Create Sales via Admin Form (Full Access)',
                'admin.purchase-form-copy' => 'Create Purchases via Admin Form (Full Access)'
            ],
            'Permission Management' => [
                'admin.permissions.index' => 'Manage User Permissions',
                'admin.permissions.store' => 'Save User Permissions',
                'admin.permissions.get' => 'Get User Permissions',
                'admin.permissions.reset' => 'Reset User Permissions'
            ],
            'User Management' => [
                'admin.users.index' => 'View All Users',
                'admin.users.create' => 'Create New User',
                'admin.users.store' => 'Save User',
                'admin.users.edit' => 'Edit User',
                'admin.users.update' => 'Update User',
                'admin.users.destroy' => 'Delete User',
                'admin.users.permissions' => 'Manage User Specific Permissions'
            ],
            'System Maintenance' => [
                'admin.backup' => 'Backup System Data',
                'admin.restore' => 'Restore System Data',
                'admin.logs' => 'View System Logs',
                'admin.clear-cache' => 'Clear System Cache'
            ]
        ],
        'Dashboard & Analytics' => [
            'Dashboard Access' => [
                'dashboard' => 'Access Main Dashboard',
                'dashboard.kpis' => 'View KPI Metrics',
                'dashboard.analytics' => 'View Analytics Dashboard'
            ],
            'Real-time Data' => [
                'dashboard.journal-entries' => 'View Recent Journal Entries',
                'dashboard.sale-chart' => 'View Sales Charts',
                'dashboard.financial-overview' => 'View Financial Overview',
                'dashboard.cash-flow-chart' => 'View Cash Flow Charts'
            ],
            'Quick Actions' => [
                'dashboard.quick-sale' => 'Quick Sale Entry',
                'dashboard.quick-expense' => 'Quick Expense Entry',
                'dashboard.quick-receipt' => 'Quick Receipt Entry',
                'dashboard.quick-payment' => 'Quick Payment Entry'
            ]
        ]
    ];

    public function index()
    {
        $user = auth()->user();
        if (!$this->isSuperAdmin($user->email)) {
            return redirect()->route('dashboard')
                ->with('error', 'Only administrators can access the permission management page.');
        }

        $users = User::all();
        $allPermissions = $this->refreshPermissions(); // Use refreshed permissions
        
        // Get all named routes grouped by module
        $routeGroups = $this->getGroupedRoutes();

        return view('admin.permissions.index', compact('users', 'routeGroups', 'allPermissions'));
    }

    private function getGroupedRoutes()
    {
        return $this->moduleGroups;
    }

    public function getUserPermissionsByEmail($email)
    {
        $user = auth()->user();
        if (!$this->isSuperAdmin($user->email)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $permissions = $this->refreshPermissions(); // Use refreshed permissions
        return response()->json([
            'permissions' => $permissions[$email]['permissions'] ?? []
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$this->isSuperAdmin($user->email)) {
            return redirect()->back()->with('error', 'Only administrators can modify permissions');
        }

        $request->validate([
            'user' => 'required|email|exists:users,email',
            'permissions' => 'array'
        ]);

        $permissions = $this->refreshPermissions(); // Use refreshed permissions
        $selectedUserEmail = $request->user;
        $newPermissions = $request->permissions ?? [];

        // Ensure the user exists in the permissions array
        if (!isset($permissions[$selectedUserEmail])) {
            $permissions[$selectedUserEmail] = [
                'permissions' => [],
                'is_super_admin' => in_array($selectedUserEmail, config('superadmins.emails', []))
            ];
        }

        // Update permissions for the selected user (this completely replaces their permissions with the new set)
        $permissions[$selectedUserEmail]['permissions'] = $newPermissions;
        $permissions[$selectedUserEmail]['is_super_admin'] = in_array($selectedUserEmail, config('superadmins.emails', []));

        // Save to JSON file
        Storage::put('user_permissions.json', json_encode($permissions, JSON_PRETTY_PRINT));

        return redirect()->back()
            ->with('message', 'Permissions updated successfully for ' . $selectedUserEmail . '. User now has ' . count($newPermissions) . ' permissions assigned.');
    }

    private function isSuperAdmin($email)
    {
        return in_array($email, config('superadmins.emails', []));
    }

    private function getUserPermissions()
    {
        if (!Storage::exists('user_permissions.json')) {
            $this->initializePermissionsFile();
        }

        return json_decode(Storage::get('user_permissions.json'), true) ?? [];
    }

    private function initializePermissionsFile()
    {
        $defaultPermissions = [
            'test@test.com' => ['permissions' => [], 'is_super_admin' => true],
            'shar@shar.com' => ['permissions' => [], 'is_super_admin' => true],
            'hp2025.final@gmail.com' => ['permissions' => [], 'is_super_admin' => true]
        ];
        Storage::put('user_permissions.json', json_encode($defaultPermissions, JSON_PRETTY_PRINT));
        return $defaultPermissions;
    }

    private function refreshPermissions()
    {
        // Force reload from storage
        if (Storage::exists('user_permissions.json')) {
            return json_decode(Storage::get('user_permissions.json'), true) ?? [];
        }
        return $this->initializePermissionsFile();
    }
}
