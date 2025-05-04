<?php
// Migration commented out to avoid duplicate table error.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // public function up(): void
    // {
    //     Schema::create('customer_receipts', function (Blueprint $table) {
    //         $table->id();
    //         $table->date('receipt_date');
    //         $table->unsignedBigInteger('customer_id');
    //         $table->decimal('amount_received', 16, 2);
    //         $table->unsignedBigInteger('bank_id');
    //         $table->text('notes')->nullable();
    //         $table->timestamps();
    //     });
    // }
    // public function down(): void
    // {
    //     Schema::dropIfExists('customer_receipts');
    // }
};
