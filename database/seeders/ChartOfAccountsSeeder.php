<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('chart_of_accounts')->insert([
            // Assets
            ['code' => '1001', 'name' => 'Cash', 'type' => 'Asset', 'parent_id' => null, 'is_group' => false],
            ['code' => '1002', 'name' => 'Bank', 'type' => 'Asset', 'parent_id' => null, 'is_group' => false],
            ['code' => '1003', 'name' => 'Inventory', 'type' => 'Asset', 'parent_id' => null, 'is_group' => false],
            ['code' => '1004', 'name' => 'Accounts Receivable (Customers)', 'type' => 'Asset', 'parent_id' => null, 'is_group' => false],
            // Liabilities
            ['code' => '2001', 'name' => 'Accounts Payable (Vendors)', 'type' => 'Liability', 'parent_id' => null, 'is_group' => false],
            ['code' => '2002', 'name' => 'Loans Payable', 'type' => 'Liability', 'parent_id' => null, 'is_group' => false],
            // Equity
            ['code' => '3001', 'name' => 'Owner’s Capital', 'type' => 'Equity', 'parent_id' => null, 'is_group' => false],
            ['code' => '3002', 'name' => 'Drawings', 'type' => 'Equity', 'parent_id' => null, 'is_group' => false],
            ['code' => '3003', 'name' => 'Opening Balance Equity', 'type' => 'Equity', 'parent_id' => null, 'is_group' => false],
            // Income
            ['code' => '4001', 'name' => 'Sales Revenue', 'type' => 'Income', 'parent_id' => null, 'is_group' => false],
            ['code' => '4002', 'name' => 'Discount Received', 'type' => 'Income', 'parent_id' => null, 'is_group' => false],
            // Expenses
            ['code' => '5001', 'name' => 'Purchases', 'type' => 'Expense', 'parent_id' => null, 'is_group' => false],
            ['code' => '5002', 'name' => 'Utilities', 'type' => 'Expense', 'parent_id' => null, 'is_group' => false],
            ['code' => '5003', 'name' => 'Rent', 'type' => 'Expense', 'parent_id' => null, 'is_group' => false],
            ['code' => '5004', 'name' => 'Discount Allowed', 'type' => 'Expense', 'parent_id' => null, 'is_group' => false],
            ['code' => '5005', 'name' => 'Cost of Goods Sold', 'type' => 'Expense', 'parent_id' => null, 'is_group' => false],
            ['code' => '5006', 'name' => 'Inventory Shrinkage', 'type' => 'Expense', 'parent_id' => null, 'is_group' => false],
        ]);
    }
}
