<?php

namespace Tests\Feature;

use App\Models\DeliveryOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProofOfDeliveryVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_order_page_shows_proof_section_for_delivered_order_with_image(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $courier = User::factory()->create(['role' => 'courier']);

        $order = DeliveryOrder::create([
            'user_id' => $user->id,
            'courier_id' => $courier->id,
            'status' => 'delivered',
            'product_description' => 'Laptop',
            'estimated_weight' => 2.20,
            'pickup_address' => 'Pickup Address',
            'delivery_address' => 'Delivery Address',
            'pod_image_path' => 'proof-of-delivery/example.png',
            'pod_image_mime' => 'image/png',
            'pod_image_size' => 1024,
            'pod_uploaded_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('user.orders.show', $order));

        $response->assertOk();
        $response->assertSee('Proof of Delivery');
        $response->assertSee('Open Full Image');
    }

    public function test_admin_dashboard_shows_proof_delivery_counts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $courier = User::factory()->create(['role' => 'courier']);

        DeliveryOrder::create([
            'user_id' => $user->id,
            'courier_id' => $courier->id,
            'status' => 'delivered',
            'product_description' => 'Item A',
            'estimated_weight' => 1.00,
            'pickup_address' => 'Pickup A',
            'delivery_address' => 'Delivery A',
            'pod_image_path' => 'proof-of-delivery/a.png',
        ]);

        DeliveryOrder::create([
            'user_id' => $user->id,
            'courier_id' => $courier->id,
            'status' => 'delivered',
            'product_description' => 'Item B',
            'estimated_weight' => 1.00,
            'pickup_address' => 'Pickup B',
            'delivery_address' => 'Delivery B',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('Proof of Delivery');
        $response->assertSee('Delivered with Proof');
        $response->assertSee('Delivered without Proof');
        $response->assertSee('1');
    }
}
