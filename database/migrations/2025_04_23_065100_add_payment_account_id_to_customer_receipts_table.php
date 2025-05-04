<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customer_receipts', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_account_id')->nullable()->after('amount_received');
            $table->foreign('payment_account_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
        });
    }
    public function down(): void
    {
        Schema::table('customer_receipts', function (Blueprint $table) {
            $table->dropForeign(['payment_account_id']);
            $table->dropColumn('payment_account_id');
        });
    }
};
