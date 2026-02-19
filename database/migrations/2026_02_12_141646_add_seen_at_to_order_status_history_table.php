<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add seen_at column to track when user has viewed the notification
     * for specific status changes (accepted, picked_up, arriving_at_dropoff, cancelled)
     */
    public function up(): void
    {
        Schema::table('order_status_history', function (Blueprint $table) {
            $table->timestamp('seen_at')
                  ->nullable()
                  ->after('created_at')
                  ->comment('When the user viewed this notification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_status_history', function (Blueprint $table) {
            $table->dropColumn('seen_at');
        });
    }
};
