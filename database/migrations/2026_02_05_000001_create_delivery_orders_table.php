<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * TABLE PURPOSE: Core table storing all delivery requests from users.
     * Links users (requesters) with couriers (acceptors) and tracks order lifecycle.
     */
    public function up(): void
    {
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            
            // User Relationship - Who created this delivery request
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->comment('User who created the delivery order');
            
            // Courier Relationship - Who accepted and will deliver this order
            $table->foreignId('courier_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('Courier assigned to this order (null = unassigned)');
            
            // Order Details - What needs to be delivered
            $table->text('product_description')
                  ->comment('Description of item(s) to be delivered');
            
            $table->decimal('estimated_weight', 8, 2)
                  ->comment('Estimated weight in kg');
            
            $table->text('special_notes')
                  ->nullable()
                  ->comment('Special handling instructions or notes');
            
            // Location Information
            $table->string('pickup_address')
                  ->comment('Where to collect the item');
            
            $table->string('pickup_contact_phone', 20)
                  ->nullable()
                  ->comment('Pickup location contact number');
            
            $table->string('delivery_address')
                  ->comment('Final destination address');
            
            $table->string('delivery_contact_phone', 20)
                  ->nullable()
                  ->comment('Delivery recipient contact number');
            
            // Status Management - Order lifecycle tracking
            $table->enum('status', [
                'pending',      // Just created, waiting for courier
                'accepted',     // Courier accepted, not yet picked up
                'in_transit',   // Item picked up, on the way
                'delivered',    // Successfully delivered
                'cancelled'     // Order cancelled (by user or courier)
            ])->default('pending')
              ->comment('Current order status');
            
            // Timestamps for Status Transitions - Audit trail
            $table->timestamp('accepted_at')
                  ->nullable()
                  ->comment('When courier accepted the order');
            
            $table->timestamp('picked_up_at')
                  ->nullable()
                  ->comment('When courier picked up the item');
            
            $table->timestamp('delivered_at')
                  ->nullable()
                  ->comment('When delivery was completed');
            
            $table->timestamp('cancelled_at')
                  ->nullable()
                  ->comment('When order was cancelled');
            
            $table->string('cancellation_reason')
                  ->nullable()
                  ->comment('Reason for cancellation');
            
            // Price Information
            $table->decimal('delivery_fee', 10, 2)
                  ->nullable()
                  ->comment('Delivery charge for this order');
            
            $table->timestamps();
            
            // Indexes for Performance
            $table->index('user_id');
            $table->index('courier_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};
