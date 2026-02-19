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
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->decimal('pickup_lat', 10, 7)->nullable()->after('pickup_contact_phone')->comment('Pickup location latitude');
            $table->decimal('pickup_lng', 10, 7)->nullable()->after('pickup_lat')->comment('Pickup location longitude');
            $table->decimal('delivery_lat', 10, 7)->nullable()->after('delivery_contact_phone')->comment('Delivery location latitude');
            $table->decimal('delivery_lng', 10, 7)->nullable()->after('delivery_lat')->comment('Delivery location longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropColumn(['pickup_lat', 'pickup_lng', 'delivery_lat', 'delivery_lng']);
        });
    }
};
