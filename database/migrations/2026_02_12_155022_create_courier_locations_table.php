<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Store courier locations at each status update point
     * Used for map visualization of courier journey
     */
    public function up(): void
    {
        Schema::create('courier_locations', function (Blueprint $table) {
            $table->id();
            
            // Order Relationship
            $table->foreignId('delivery_order_id')
                  ->constrained('delivery_orders')
                  ->onDelete('cascade')
                  ->comment('Which order this location is for');
            
            // Courier Relationship
            $table->foreignId('courier_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->comment('Which courier is at this location');
            
            // Location Data
            $table->decimal('latitude', 10, 8)
                  ->comment('Location latitude');
            
            $table->decimal('longitude', 11, 8)
                  ->comment('Location longitude');
            
            $table->string('address', 500)
                  ->nullable()
                  ->comment('Human-readable address');
            
            // Status at this location
            $table->string('status_at_location', 50)
                  ->comment('Order status when location was recorded');
            
            // Additional context
            $table->text('notes')
                  ->nullable()
                  ->comment('Notes about this location update');
            
            $table->timestamp('created_at')->nullable();
            
            // Indexes
            $table->index('delivery_order_id');
            $table->index('courier_id');
            $table->index('created_at');
            $table->index(['delivery_order_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_locations');
    }
};
