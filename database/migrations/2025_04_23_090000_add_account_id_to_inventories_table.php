<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable()->after('notes');
        });
    }
    public function down()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn('account_id');
        });
    }
};
