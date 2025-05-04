<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->constrained('product_categories');
            $table->foreignId('unit_id')->constrained('units');
            $table->decimal('opening_quantity', 18, 2)->nullable();
            $table->decimal('opening_rate', 18, 2)->nullable();
            $table->decimal('opening_value', 18, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
