<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * TABLE PURPOSE: Audit trail for all status changes in delivery orders.
     * Tracks WHO changed WHAT and WHEN for accountability and debugging.
     * Useful for dispute resolution and performance analytics.
     */
    public function up(): void
    {
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            
            // Order Relationship
            $table->foreignId('delivery_order_id')
                  ->constrained('delivery_orders')
                  ->onDelete('cascade')
                  ->comment('Which order status changed');
            
            // Status Transition
            $table->string('old_status')
                  ->nullable()
                  ->comment('Previous status (null for initial creation)');
            
            $table->string('new_status')
                  ->comment('New status after change');
            
            // Who Made the Change
            $table->foreignId('changed_by')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->comment('User who triggered this status change');
            
            // Additional Context
            $table->text('reason')
                  ->nullable()
                  ->comment('Reason for status change (especially for cancellations)');
            
            $table->text('notes')
                  ->nullable()
                  ->comment('Additional notes about this transition');
            
            // Location snapshot (useful for transit tracking)
            $table->string('location_lat')
                  ->nullable()
                  ->comment('Latitude when status changed');
            
            $table->string('location_lng')
                  ->nullable()
                  ->comment('Longitude when status changed');
            
            $table->timestamp('created_at');
            
            // Indexes
            $table->index('delivery_order_id');
            $table->index('changed_by');
            $table->index(['delivery_order_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
    }
};
