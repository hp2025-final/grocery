<?php

namespace App\Permissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Permissions
        $permissions = [
            // Sales
            'view_sales', 'create_sales', 'edit_sales', 'delete_sales',
            // Purchases
            'view_purchases', 'create_purchases', 'edit_purchases', 'delete_purchases',
            // Expenses
            'view_expenses', 'create_expenses', 'edit_expenses', 'delete_expenses',
            // Receipts
            'view_receipts', 'create_receipts', 'edit_receipts', 'delete_receipts',
            // Payments
            'view_payments', 'create_payments', 'edit_payments', 'delete_payments',
            // Chart of Accounts
            'manage_chart_of_accounts',
            // Inventory
            'view_inventory', 'adjust_inventory',
            // Reports
            'view_reports',
            // Users
            'manage_users',
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Roles
        $owner = Role::firstOrCreate(['name' => 'Owner']);
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $cashier = Role::firstOrCreate(['name' => 'Cashier']);

        // Assign all permissions to Owner
        $owner->syncPermissions($permissions);

        // Manager permissions (all except manage_users, manage_chart_of_accounts)
        $managerPerms = array_diff($permissions, ['manage_users', 'manage_chart_of_accounts']);
        $manager->syncPermissions($managerPerms);

        // Cashier permissions (sales, receipts, view inventory, view reports)
        $cashierPerms = [
            'view_sales', 'create_sales',
            'view_receipts', 'create_receipts',
            'view_inventory', 'view_reports',
        ];
        $cashier->syncPermissions($cashierPerms);

        // Optionally, assign Owner role to first user
        $user = User::first();
        if ($user) {
            $user->assignRole('Owner');
        }
    }
}
