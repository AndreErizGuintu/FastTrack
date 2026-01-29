<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('who')->nullable()->after('detail');
            $table->string('warehouse')->nullable()->after('who');
            $table->string('courier_name')->nullable()->after('warehouse');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('who');
            $table->dropColumn('warehouse');
            $table->dropColumn('courier_name');
        });
    }
};
