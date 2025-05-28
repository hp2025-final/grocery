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
            'Add Sale' => [
                'sales.create' => 'Create New Sale Invoice',
                'sales.store' => 'Save Sale Invoice',
                'sales.edit' => 'Edit Sale Invoice',
                'sales.update' => 'Update Sale Invoice'
            ],
            'All Sale Invoices' => [
                'sales.index' => 'View All Sale Invoices',
                'sales.show' => 'View Single Sale Invoice',
                'sales.pdf' => 'Print Sale Invoice',
                'sales.destroy' => 'Delete Sale Invoice'
            ],
            'Customer Receipt' => [
                'customer-receipts.create' => 'Create New Receipt',
                'customer-receipts.store' => 'Save Receipt',
                'customer-receipts.edit' => 'Edit Receipt',
                'customer-receipts.update' => 'Update Receipt',
                'customer-receipts.destroy' => 'Delete Receipt'
            ],
            'Add Customer' => [
                'customers.create' => 'Create New Customer',
                'customers.store' => 'Save Customer',
                'customers.edit' => 'Edit Customer',
                'customers.update' => 'Update Customer'
            ],
            'View Balances' => [
                'customer-balances.index' => 'View Customer Balances',
                'customer-balances.export' => 'Export Customer Balances',
                'customers.ledger' => 'View Customer Ledger'
            ]
        ],
        'Payables' => [
            'Add Purchase' => [
                'purchases.create' => 'Create New Purchase Invoice',
                'purchases.store' => 'Save Purchase Invoice',
                'purchases.edit' => 'Edit Purchase Invoice',
                'purchases.update' => 'Update Purchase Invoice'
            ],
            'All Purchases' => [
                'purchases.index' => 'View All Purchases',
                'purchases.show' => 'View Single Purchase',
                'purchases.pdf' => 'Print Purchase Invoice',
                'purchases.destroy' => 'Delete Purchase'
            ],
            'Vendor Payment' => [
                'vendor-payments.create' => 'Create New Payment',
                'vendor-payments.store' => 'Save Payment',
                'vendor-payments.show' => 'View Payment',
                'vendor-payments.destroy' => 'Delete Payment'
            ],
            'Add Vendor' => [
                'vendors.create' => 'Create New Vendor',
                'vendors.store' => 'Save Vendor',
                'vendors.edit' => 'Edit Vendor',
                'vendors.update' => 'Update Vendor'
            ],
            'View Balances' => [
                'vendor-balances.index' => 'View Vendor Balances',
                'vendor-balances.export' => 'Export Vendor Balances',
                'vendors.ledger' => 'View Vendor Ledger'
            ]
        ],
        'Inventory' => [
            'Add Product' => [
                'inventory.create' => 'Create New Product',
                'inventory.store' => 'Save Product',
                'inventory.edit' => 'Edit Product',
                'inventory.update' => 'Update Product',
                'inventory.destroy' => 'Delete Product'
            ],
            'All Products' => [
                'inventory.index' => 'View All Products'
            ],
            'Categories' => [
                'inventory-categories.index' => 'View Categories',
                'inventory-categories.create' => 'Create New Category',
                'inventory-categories.store' => 'Save Category',
                'inventory-categories.edit' => 'Edit Category',
                'inventory-categories.update' => 'Update Category',
                'inventory-categories.destroy' => 'Delete Category'
            ],
            'Stock Adjustment' => [
                'stock-adjustments.create' => 'Create New Adjustment',
                'stock-adjustments.store' => 'Save Adjustment',
                'stock-adjustments.index' => 'View All Adjustments'
            ],
            'Reports' => [
                'inventory-balances.index' => 'View Stock Balances',
                'inventory-balances.export' => 'Export Stock Balances',
                'inventory-values.index' => 'View Stock Values',
                'inventory-values.export-pdf' => 'Export Stock Values PDF',
                'inventory-values.info' => 'View Stock Information'
            ]
        ],
        'Banking' => [
            'Bank Account' => [
                'banks.create' => 'Create New Bank Account',
                'banks.store' => 'Save Bank Account',
                'banks.edit' => 'Edit Bank Account',
                'banks.update' => 'Update Bank Account',
                'banks.destroy' => 'Delete Bank Account',
                'banks.index' => 'View All Bank Accounts'
            ],
            'Bank Transfer' => [
                'bank_transfers.create' => 'Create New Transfer',
                'bank_transfers.store' => 'Save Transfer',
                'bank_transfers.destroy' => 'Delete Transfer'
            ],
            'Reports' => [
                'bank-balances.index' => 'View Bank Balances',
                'bank-balances.export' => 'Export Bank Report',
                'banks.ledger' => 'View Bank Ledger'
            ]
        ],
        'Expenses' => [
            'Add Expense' => [
                'expenses.create' => 'Create New Expense',
                'expenses.store' => 'Save Expense',
                'expenses.edit' => 'Edit Expense',
                'expenses.destroy' => 'Delete Expense'
            ],
            'All Expenses' => [
                'expenses.index' => 'View All Expenses'
            ],
            'Account Setup' => [
                'expense-accounts.create' => 'Create New Account',
                'expense-accounts.store' => 'Save Account',
                'expense-accounts.index' => 'View All Accounts'
            ],
            'Reports' => [
                'accounts.ledger' => 'View Expense Ledger'
            ]
        ],
        'Reports' => [
            'Financial Reports' => [
                'reports.trial_balance' => 'View Trial Balance',
                'reports.general_ledger' => 'View General Ledger',
                'reports.journal' => 'View Journal Entries',
                'reports.income_statement' => 'View Income Statement',
                'reports.balance_sheet' => 'View Balance Sheet',
                'reports.customer_list' => 'View Customer List'
            ]
        ],
        'Admin' => [
            'Settings' => [
                'admin.index' => 'Access Admin Dashboard',
                'admin.sales-form-copy' => 'View Sales Form Settings',
                'admin.purchase-form-copy' => 'View Purchase Form Settings',
                'admin.permissions.index' => 'Manage User Permissions',
                'admin.permissions.store' => 'Save User Permissions'
            ]
        ],
        'Dashboard' => [
            'Analytics' => [
                'dashboard.kpis' => 'View KPI Metrics',
                'dashboard.journal-entries' => 'View Recent Entries',
                'dashboard.sale-chart' => 'View Sales Charts'
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
        $allPermissions = $this->getUserPermissions();
        
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

        $permissions = $this->getUserPermissions();
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

        $permissions = $this->getUserPermissions();

        // Update permissions for the user
        $permissions[$request->user] = [
            'permissions' => $request->permissions ?? [],
            'is_super_admin' => in_array($request->user, config('superadmins.emails', []))
        ];

        // Save to JSON file
        Storage::put('user_permissions.json', json_encode($permissions, JSON_PRETTY_PRINT));

        return redirect()->back()->with('message', 'Permissions updated successfully');
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
}
