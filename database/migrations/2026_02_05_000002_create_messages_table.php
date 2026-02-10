<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * TABLE PURPOSE: Stores all chat messages between users and their assigned couriers.
     * Each message is tied to a specific delivery order, ensuring privacy and context.
     * Chat is ONLY accessible to the user who created the order and the courier who accepted it.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            
            // Order Relationship - Which delivery order is this chat about
            $table->foreignId('delivery_order_id')
                  ->constrained('delivery_orders')
                  ->onDelete('cascade')
                  ->comment('Links message to specific delivery order');
            
            // Sender Relationship - Who sent this message (user or courier)
            $table->foreignId('sender_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->comment('User who sent this message');
            
            // Message Content
            $table->text('message')
                  ->comment('The actual message text');
            
            // Message Metadata
            $table->boolean('is_read')
                  ->default(false)
                  ->comment('Has the recipient read this message');
            
            $table->timestamp('read_at')
                  ->nullable()
                  ->comment('When message was marked as read');
            
            // Optional: Message Type (text, image, location)
            $table->enum('message_type', ['text', 'image', 'location'])
                  ->default('text')
                  ->comment('Type of message content');
            
            // Optional: Attachment storage
            $table->string('attachment_path')
                  ->nullable()
                  ->comment('Path to uploaded file/image if any');
            
            $table->timestamps();
            
            // Indexes for Performance
            $table->index('delivery_order_id');
            $table->index('sender_id');
            $table->index(['delivery_order_id', 'created_at']); // For fetching messages in order
            $table->index(['sender_id', 'is_read']); // For unread message counts
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
