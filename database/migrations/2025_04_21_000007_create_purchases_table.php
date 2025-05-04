<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_number')->unique();
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->date('purchase_date');
            $table->decimal('total_amount', 18, 2);
            $table->decimal('discount_amount', 18, 2)->nullable();
            $table->decimal('net_amount', 18, 2);
            $table->enum('payment_status', ['Paid', 'Unpaid', 'Partial']);
            $table->foreignId('payment_account_id')->nullable()->constrained('chart_of_accounts');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
