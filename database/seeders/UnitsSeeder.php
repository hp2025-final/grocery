<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('units')->insert([
            ['name' => 'Kilogram', 'abbreviation' => 'kg'],
            ['name' => 'Gram', 'abbreviation' => 'g'],
            ['name' => 'Piece', 'abbreviation' => 'pcs'],
            ['name' => 'Pack', 'abbreviation' => 'pack'],
            ['name' => 'Liter', 'abbreviation' => 'l'],
            ['name' => 'Dozen', 'abbreviation' => 'doz'],
            ['name' => 'Bottle', 'abbreviation' => 'btl'],
            ['name' => 'Can', 'abbreviation' => 'can'],
        ]);
    }
}
