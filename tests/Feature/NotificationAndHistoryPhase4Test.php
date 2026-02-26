<?php

namespace Tests\Feature;

use App\Models\DeliveryOrder;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationAndHistoryPhase4Test extends TestCase
{
    use RefreshDatabase;

    public function test_mark_all_seen_includes_delivered_notifications(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $courier = User::factory()->create(['role' => 'courier']);

        $order = DeliveryOrder::create([
            'user_id' => $user->id,
            'courier_id' => $courier->id,
            'status' => 'delivered',
            'product_description' => 'Package',
            'estimated_weight' => 1.20,
            'pickup_address' => 'Pickup',
            'delivery_address' => 'Dropoff',
        ]);

        $history = OrderStatusHistory::create([
            'delivery_order_id' => $order->id,
            'old_status' => 'at_dropoff',
            'new_status' => 'delivered',
            'changed_by' => $courier->id,
            'actor_type' => 'courier',
            'reason' => 'Delivered',
        ]);

        $response = $this->actingAs($user)->post(route('notifications.mark-all-seen'));

        $response->assertOk();

        $this->assertNotNull($history->fresh()->seen_at);
    }

    public function test_delivery_transition_creates_single_history_record(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $courier = User::factory()->create(['role' => 'courier']);

        $order = DeliveryOrder::create([
            'user_id' => $user->id,
            'courier_id' => $courier->id,
            'status' => 'at_dropoff',
            'product_description' => 'Package',
            'estimated_weight' => 1.20,
            'pickup_address' => 'Pickup',
            'delivery_address' => 'Dropoff',
        ]);

        $this->actingAs($courier)->post(route('courier.deliver', $order));

        $this->assertSame(1, OrderStatusHistory::where('delivery_order_id', $order->id)
            ->where('new_status', 'delivered')
            ->count());
    }
}
