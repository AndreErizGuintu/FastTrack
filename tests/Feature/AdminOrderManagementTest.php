<?php

namespace Tests\Feature;

use App\Models\DeliveryOrder;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_orders_index_and_show_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $courier = User::factory()->create(['role' => 'courier']);

        $order = DeliveryOrder::create([
            'user_id' => $user->id,
            'courier_id' => $courier->id,
            'status' => 'accepted',
            'product_description' => 'Gaming Console',
            'estimated_weight' => 3.50,
            'pickup_address' => 'Pickup Address',
            'pickup_contact_phone' => '+63 9123456789',
            'delivery_address' => 'Delivery Address',
            'delivery_contact_phone' => '+63 9987654321',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('Manage Orders')
            ->assertSee((string) $order->id);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Admin Status Override')
            ->assertSee($order->tracking_id);
    }

    public function test_non_admin_cannot_access_admin_orders_routes(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $orderOwner = User::factory()->create(['role' => 'user']);

        $order = DeliveryOrder::create([
            'user_id' => $orderOwner->id,
            'status' => 'draft',
            'product_description' => 'Laptop',
            'estimated_weight' => 2.00,
            'pickup_address' => 'Pickup Address',
            'pickup_contact_phone' => '+63 9123456789',
            'delivery_address' => 'Delivery Address',
            'delivery_contact_phone' => '+63 9987654321',
        ]);

        $this->actingAs($user)
            ->get(route('admin.orders.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.orders.show', $order))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.orders.status.update', $order), [
                'status' => 'accepted',
                'reason' => 'Unauthorized',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_override_order_status_and_history_is_logged(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $order = DeliveryOrder::create([
            'user_id' => $user->id,
            'status' => 'draft',
            'product_description' => 'Phone',
            'estimated_weight' => 1.20,
            'pickup_address' => 'Pickup Address',
            'pickup_contact_phone' => '+63 9123456789',
            'delivery_address' => 'Delivery Address',
            'delivery_contact_phone' => '+63 9987654321',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.orders.status.update', $order), [
                'status' => 'delivered',
                'reason' => 'Manual correction by admin',
            ]);

        $response->assertRedirect(route('admin.orders.show', $order));

        $order->refresh();
        $this->assertSame('delivered', $order->status);

        $history = OrderStatusHistory::where('delivery_order_id', $order->id)
            ->where('new_status', 'delivered')
            ->latest('id')
            ->first();

        $this->assertNotNull($history);
        $this->assertSame('admin', $history->actor_type);
        $this->assertSame('Manual correction by admin', $history->reason);
    }

    public function test_admin_orders_index_can_filter_by_tracking_id(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $matching = DeliveryOrder::create([
            'user_id' => $user->id,
            'status' => 'draft',
            'tracking_id' => 'FT-202602-ABCDE',
            'product_description' => 'Tablet',
            'estimated_weight' => 1.00,
            'pickup_address' => 'Pickup A',
            'pickup_contact_phone' => '+63 9123456789',
            'delivery_address' => 'Dropoff A',
            'delivery_contact_phone' => '+63 9987654321',
        ]);

        DeliveryOrder::create([
            'user_id' => $user->id,
            'status' => 'draft',
            'tracking_id' => 'FT-202602-ZYXWV',
            'product_description' => 'Speaker',
            'estimated_weight' => 1.00,
            'pickup_address' => 'Pickup B',
            'pickup_contact_phone' => '+63 9123456789',
            'delivery_address' => 'Dropoff B',
            'delivery_contact_phone' => '+63 9987654321',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders.index', ['tracking_id' => 'ABCDE']))
            ->assertOk()
            ->assertSee($matching->tracking_id)
            ->assertDontSee('FT-202602-ZYXWV');
    }

    public function test_admin_orders_index_shows_quick_status_filter_links(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        DeliveryOrder::create([
            'user_id' => $user->id,
            'status' => 'draft',
            'product_description' => 'Test Item',
            'estimated_weight' => 1.00,
            'pickup_address' => 'Pickup',
            'pickup_contact_phone' => '+63 9123456789',
            'delivery_address' => 'Delivery',
            'delivery_contact_phone' => '+63 9987654321',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('Quick Status')
            ->assertSee('status=draft', false)
            ->assertSee('status=delivered', false);
    }
}
