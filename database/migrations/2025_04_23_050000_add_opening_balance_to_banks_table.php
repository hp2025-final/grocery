<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->decimal('opening_balance', 15, 2)->nullable()->after('swift_code');
        });
    }
    public function down(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->dropColumn('opening_balance');
        });
    }
};
