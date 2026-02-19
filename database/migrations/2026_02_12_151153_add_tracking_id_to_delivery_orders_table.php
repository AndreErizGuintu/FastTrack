<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add tracking_id column for unique order tracking
     * Format: FT-YYYYMM-XXXXX (e.g., FT-202602-A3K9L)
     */
    public function up(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->string('tracking_id', 20)
                  ->unique()
                  ->after('id')
                  ->comment('Unique tracking identifier for the order');
            
            $table->index('tracking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropIndex(['tracking_id']);
            $table->dropColumn('tracking_id');
        });
    }
};
