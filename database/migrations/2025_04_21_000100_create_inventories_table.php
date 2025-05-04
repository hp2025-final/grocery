<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('inventory_code')->unique(); // PRD-000001
            $table->string('name');
            $table->unsignedBigInteger('category_id');
            $table->string('unit');
            $table->decimal('buy_price', 15, 2);
            $table->decimal('sale_price', 15, 2);
            $table->decimal('opening_qty', 15, 2)->nullable();
            $table->unsignedBigInteger('opening_account_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('inventory_categories');
            $table->foreign('opening_account_id')->references('id')->on('chart_of_accounts');
        });
    }
    public function down() {
        Schema::dropIfExists('inventories');
    }
};
