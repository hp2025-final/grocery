<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddMissingChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['code' => '3003', 'name' => 'Opening Balance Equity', 'type' => 'Equity'],
            ['code' => '5005', 'name' => 'Cost of Goods Sold', 'type' => 'Expense'],
            ['code' => '5006', 'name' => 'Inventory Shrinkage', 'type' => 'Expense'],
        ];
        foreach ($accounts as $acc) {
            $exists = DB::table('chart_of_accounts')->where('code', $acc['code'])->exists();
            if (!$exists) {
                DB::table('chart_of_accounts')->insert([
                    'code' => $acc['code'],
                    'name' => $acc['name'],
                    'type' => $acc['type'],
                    'parent_id' => null,
                    'is_group' => false,
                ]);
            }
        }
    }
}
