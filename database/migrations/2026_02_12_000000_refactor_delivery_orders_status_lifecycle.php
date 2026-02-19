<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * OBJECTIVE: Refactor delivery_orders.status into a logistics-grade state machine
     * with strict transition validation and comprehensive operational timestamps.
     * 
     * BACKWARD COMPATIBILITY:
     * - Existing 'pending' → 'awaiting_courier'
     * - Existing 'accepted' → 'accepted' (no change)
     * - Existing 'in_transit' → 'in_transit' (or picked_up if picked_up_at exists)
     * - Existing 'delivered' → 'delivered' (no change)
     * - Existing 'cancelled' → 'cancelled_by_user' (conservative assumption)
     * 
     * DOES NOT DROP TABLES OR RESET DATA
     */
    public function up(): void
    {
        // Step 1: Change status column to VARCHAR temporary (to avoid enum constraints during migration)
        DB::statement("ALTER TABLE delivery_orders MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'awaiting_courier'");

        // Step 2: Migrate data from old statuses to new ones
        DB::table('delivery_orders')
            ->where('status', 'pending')
            ->update(['status' => 'awaiting_courier']);

        // accepted stays as 'accepted' (already maps)
        
        // in_transit → picked_up if picked_up_at is set, else stay in_transit
        DB::statement("UPDATE delivery_orders SET status = 'picked_up' WHERE status = 'in_transit' AND picked_up_at IS NOT NULL");

        // delivered stays as 'delivered' (already maps)
        
        // cancelled → cancelled_by_user (conservative assumption)
        DB::table('delivery_orders')
            ->where('status', 'cancelled')
            ->update(['status' => 'cancelled_by_user']);

        // Step 3: Now change back to ENUM with all new statuses
        DB::statement("ALTER TABLE delivery_orders MODIFY COLUMN status ENUM(
            'draft',
            'awaiting_courier',
            'courier_assigned',
            'accepted',
            'arriving_at_pickup',
            'at_pickup',
            'picked_up',
            'in_transit',
            'arriving_at_dropoff',
            'at_dropoff',
            'delivered',
            'delivery_failed',
            'returned',
            'cancelled_by_user',
            'cancelled_by_courier',
            'cancelled_by_system',
            'expired'
        ) NOT NULL DEFAULT 'awaiting_courier'");

        // Step 4: Add new operational timestamps (without DB-level comments for MariaDB compatibility)
        if (!Schema::hasColumn('delivery_orders', 'arriving_at_pickup_at')) {
            DB::statement("ALTER TABLE delivery_orders ADD COLUMN arriving_at_pickup_at TIMESTAMP NULL AFTER accepted_at");
        }
        
        if (!Schema::hasColumn('delivery_orders', 'at_pickup_at')) {
            DB::statement("ALTER TABLE delivery_orders ADD COLUMN at_pickup_at TIMESTAMP NULL AFTER arriving_at_pickup_at");
        }
        
        if (!Schema::hasColumn('delivery_orders', 'arriving_at_dropoff_at')) {
            DB::statement("ALTER TABLE delivery_orders ADD COLUMN arriving_at_dropoff_at TIMESTAMP NULL AFTER picked_up_at");
        }
        
        if (!Schema::hasColumn('delivery_orders', 'at_dropoff_at')) {
            DB::statement("ALTER TABLE delivery_orders ADD COLUMN at_dropoff_at TIMESTAMP NULL AFTER arriving_at_dropoff_at");
        }
        
        if (!Schema::hasColumn('delivery_orders', 'delivery_failed_at')) {
            DB::statement("ALTER TABLE delivery_orders ADD COLUMN delivery_failed_at TIMESTAMP NULL AFTER at_dropoff_at");
        }
        
        if (!Schema::hasColumn('delivery_orders', 'returned_at')) {
            DB::statement("ALTER TABLE delivery_orders ADD COLUMN returned_at TIMESTAMP NULL AFTER delivery_failed_at");
        }
        
        if (!Schema::hasColumn('delivery_orders', 'expired_at')) {
            DB::statement("ALTER TABLE delivery_orders ADD COLUMN expired_at TIMESTAMP NULL AFTER returned_at");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Change status back to VARCHAR for data migration
        DB::statement("ALTER TABLE delivery_orders MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'pending'");

        // Step 2: Rollback data
        DB::table('delivery_orders')
            ->where('status', 'awaiting_courier')
            ->update(['status' => 'pending']);

        DB::table('delivery_orders')
            ->where('status', 'picked_up')
            ->update(['status' => 'in_transit']);

        DB::table('delivery_orders')
            ->whereIn('status', [
                'cancelled_by_user',
                'cancelled_by_courier',
                'cancelled_by_system',
            ])
            ->update(['status' => 'cancelled']);

        // Step 3: Change back to original enum
        DB::statement("ALTER TABLE delivery_orders MODIFY COLUMN status ENUM(
            'pending',
            'accepted',
            'in_transit',
            'delivered',
            'cancelled'
        ) NOT NULL DEFAULT 'pending'");

        // Step 4: Drop new timestamp columns
        DB::statement("ALTER TABLE delivery_orders DROP COLUMN IF EXISTS arriving_at_pickup_at");
        DB::statement("ALTER TABLE delivery_orders DROP COLUMN IF EXISTS at_pickup_at");
        DB::statement("ALTER TABLE delivery_orders DROP COLUMN IF EXISTS arriving_at_dropoff_at");
        DB::statement("ALTER TABLE delivery_orders DROP COLUMN IF EXISTS at_dropoff_at");
        DB::statement("ALTER TABLE delivery_orders DROP COLUMN IF EXISTS delivery_failed_at");
        DB::statement("ALTER TABLE delivery_orders DROP COLUMN IF EXISTS returned_at");
        DB::statement("ALTER TABLE delivery_orders DROP COLUMN IF EXISTS expired_at");
    }
};
