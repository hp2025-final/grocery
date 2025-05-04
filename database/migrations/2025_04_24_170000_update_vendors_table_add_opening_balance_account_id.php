<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->renameColumn('contact', 'phone');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->string('opening_type')->default('credit');
            $table->unsignedBigInteger('account_id')->nullable()->after('id');
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->renameColumn('phone', 'contact');
            $table->dropColumn(['opening_balance', 'opening_type']);
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
    }
};
