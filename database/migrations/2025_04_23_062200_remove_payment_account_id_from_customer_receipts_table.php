<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customer_receipts', function (Blueprint $table) {
            if (Schema::hasColumn('customer_receipts', 'payment_account_id')) {
                $table->dropForeign(['payment_account_id']);
                $table->dropColumn('payment_account_id');
            }
        });
    }
    public function down(): void
    {
        Schema::table('customer_receipts', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_account_id')->nullable()->after('amount_received');
            // You may want to re-add the foreign key if needed
            // $table->foreign('payment_account_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
        });
    }
};
