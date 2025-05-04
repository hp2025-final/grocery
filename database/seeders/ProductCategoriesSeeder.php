<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('product_categories')->insert([
            ['name' => 'Rice'],
            ['name' => 'Bread'],
            ['name' => 'Cooking Oil'],
            ['name' => 'Flour'],
            ['name' => 'Dairy'],
            ['name' => 'Snacks'],
            ['name' => 'Beverages'],
            ['name' => 'Spices'],
            ['name' => 'Household'],
            ['name' => 'Frozen Foods'],
        ]);
    }
}
