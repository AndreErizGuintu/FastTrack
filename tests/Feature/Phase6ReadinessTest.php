<?php

namespace Tests\Feature;

use App\Models\DeliveryOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Phase6ReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_orders_schema_contains_phase2_and_lifecycle_columns(): void
    {
        $columns = [
            'tracking_id',
            'pickup_lat',
            'pickup_lng',
            'delivery_lat',
            'delivery_lng',
            'arriving_at_pickup_at',
            'at_pickup_at',
            'arriving_at_dropoff_at',
            'at_dropoff_at',
            'delivery_failed_at',
            'returned_at',
            'expired_at',
            'pod_image_path',
            'pod_image_mime',
            'pod_image_size',
            'pod_uploaded_at',
        ];

        foreach ($columns as $column) {
            $this->assertTrue(Schema::hasColumn('delivery_orders', $column), "Missing column: {$column}");
        }
    }

    public function test_current_schema_accepts_extended_status_values(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $courier = User::factory()->create(['role' => 'courier']);

        $order = DeliveryOrder::create([
            'user_id' => $user->id,
            'courier_id' => $courier->id,
            'status' => 'at_dropoff',
            'product_description' => 'Status Check Item',
            'estimated_weight' => 1.10,
            'pickup_address' => 'Pickup',
            'pickup_contact_phone' => '+63 9123456789',
            'delivery_address' => 'Dropoff',
            'delivery_contact_phone' => '+63 9987654321',
        ]);

        $this->assertSame('at_dropoff', $order->fresh()->status);
    }

    public function test_chat_route_is_accessible_only_to_order_participants(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $courier = User::factory()->create(['role' => 'courier']);
        $outsider = User::factory()->create(['role' => 'user']);

        $order = DeliveryOrder::create([
            'user_id' => $owner->id,
            'courier_id' => $courier->id,
            'status' => 'accepted',
            'product_description' => 'Chat Access Item',
            'estimated_weight' => 1.00,
            'pickup_address' => 'Pickup',
            'pickup_contact_phone' => '+63 9123456789',
            'delivery_address' => 'Dropoff',
            'delivery_contact_phone' => '+63 9987654321',
        ]);

        $this->actingAs($owner)
            ->get(route('orders.chat', $order) . '?partial=1')
            ->assertOk();

        $this->actingAs($courier)
            ->get(route('orders.chat', $order) . '?partial=1')
            ->assertOk();

        $this->actingAs($outsider)
            ->get(route('orders.chat', $order) . '?partial=1')
            ->assertForbidden();
    }
}
