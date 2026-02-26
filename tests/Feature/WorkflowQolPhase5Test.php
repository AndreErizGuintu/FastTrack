<?php

namespace Tests\Feature;

use App\Models\DeliveryOrder;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowQolPhase5Test extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_order_with_coordinates(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->post(route('user.orders.store'), [
            'product_description' => 'Camera',
            'estimated_weight' => 1.25,
            'pickup_address' => 'Pickup Street',
            'pickup_contact_phone' => '+63 9123456789',
            'pickup_lat' => 14.5995,
            'pickup_lng' => 120.9842,
            'delivery_address' => 'Delivery Street',
            'delivery_contact_phone' => '+63 9987654321',
            'delivery_lat' => 14.5547,
            'delivery_lng' => 121.0244,
            'delivery_fee' => 120.50,
        ]);

        $response->assertRedirect(route('user.dashboard'));

        $order = DeliveryOrder::latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals(14.5995, (float) $order->pickup_lat);
        $this->assertEquals(120.9842, (float) $order->pickup_lng);
        $this->assertEquals(14.5547, (float) $order->delivery_lat);
        $this->assertEquals(121.0244, (float) $order->delivery_lng);
    }

    public function test_order_modal_endpoint_returns_partial_content(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $order = DeliveryOrder::create([
            'user_id' => $user->id,
            'status' => 'draft',
            'product_description' => 'Laptop',
            'estimated_weight' => 2.00,
            'pickup_address' => 'Pickup',
            'pickup_contact_phone' => '+63 9123456789',
            'delivery_address' => 'Dropoff',
            'delivery_contact_phone' => '+63 9987654321',
        ]);

        $response = $this->actingAs($user)->get(route('user.orders.show', $order) . '?modal=1');

        $response->assertOk();
        $response->assertSee('Tracking ID');
        $response->assertSee('Open Full Details');
        $response->assertDontSee('<html', false);
    }

    public function test_chat_partial_endpoint_returns_message_markup_only(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $courier = User::factory()->create(['role' => 'courier']);

        $order = DeliveryOrder::create([
            'user_id' => $user->id,
            'courier_id' => $courier->id,
            'status' => 'accepted',
            'product_description' => 'Package',
            'estimated_weight' => 1.00,
            'pickup_address' => 'Pickup',
            'pickup_contact_phone' => '+63 9123456789',
            'delivery_address' => 'Dropoff',
            'delivery_contact_phone' => '+63 9987654321',
        ]);

        Message::create([
            'delivery_order_id' => $order->id,
            'sender_id' => $courier->id,
            'message' => 'On my way',
            'message_type' => 'text',
        ]);

        $response = $this->actingAs($user)->get(route('orders.chat', $order) . '?partial=1');

        $response->assertOk();
        $response->assertSee('On my way');
        $response->assertDontSee('<html', false);
    }
}
