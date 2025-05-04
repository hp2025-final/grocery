<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        // Get category and unit IDs
        $riceCat = DB::table('product_categories')->where('name', 'Rice')->value('id');
        $dairyCat = DB::table('product_categories')->where('name', 'Dairy')->value('id');
        $beveragesCat = DB::table('product_categories')->where('name', 'Beverages')->value('id');
        $kgUnit = DB::table('units')->where('abbreviation', 'kg')->value('id');
        $literUnit = DB::table('units')->where('abbreviation', 'l')->value('id');
        $pcsUnit = DB::table('units')->where('abbreviation', 'pcs')->value('id');
        
        DB::table('products')->insert([
            [
                'name' => 'Basmati Rice 5kg',
                'category_id' => $riceCat,
                'unit_id' => $kgUnit,
                'opening_quantity' => 10,
                'opening_rate' => 300,
                'opening_value' => 3000,
            ],
            [
                'name' => 'Milk 1L',
                'category_id' => $dairyCat,
                'unit_id' => $literUnit,
                'opening_quantity' => 20,
                'opening_rate' => 150,
                'opening_value' => 3000,
            ],
            [
                'name' => 'Soft Drink 1.5L',
                'category_id' => $beveragesCat,
                'unit_id' => $literUnit,
                'opening_quantity' => 15,
                'opening_rate' => 100,
                'opening_value' => 1500,
            ],
            [
                'name' => 'Bread Large',
                'category_id' => DB::table('product_categories')->where('name', 'Bread')->value('id'),
                'unit_id' => $pcsUnit,
                'opening_quantity' => 25,
                'opening_rate' => 80,
                'opening_value' => 2000,
            ],
            [
                'name' => 'Cooking Oil 5L',
                'category_id' => DB::table('product_categories')->where('name', 'Cooking Oil')->value('id'),
                'unit_id' => $literUnit,
                'opening_quantity' => 8,
                'opening_rate' => 1800,
                'opening_value' => 14400,
            ],
        ]);
    }
}
