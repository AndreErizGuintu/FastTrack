<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add actor_type column to order_status_history for audit trail
     * Distinguishes between user, courier, system, and admin actions
     */
    public function up(): void
    {
        Schema::table('order_status_history', function (Blueprint $table) {
            $table->enum('actor_type', ['user', 'courier', 'system', 'admin'])
                  ->default('system')
                  ->after('changed_by')
                  ->comment('Type of actor who triggered the status change');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_status_history', function (Blueprint $table) {
            $table->dropColumn('actor_type');
        });
    }
};
