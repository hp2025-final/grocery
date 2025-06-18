<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->enum('payment_status', ['Paid', 'Unpaid'])->default('Unpaid')->after('net_amount');
        });

        // Update all existing purchases to Unpaid by default
        DB::table('purchases')->update(['payment_status' => 'Unpaid']);
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }
};
