<?php

namespace Tests\Feature;

use App\Models\DeliveryOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourierProofOfDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_courier_can_upload_proof_image_when_marking_delivered(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'user']);
        $courier = User::factory()->create(['role' => 'courier']);

        $order = DeliveryOrder::create([
            'user_id' => $user->id,
            'courier_id' => $courier->id,
            'status' => 'at_dropoff',
            'product_description' => 'Phone',
            'estimated_weight' => 1.50,
            'pickup_address' => 'Pickup Point',
            'delivery_address' => 'Delivery Point',
        ]);

        $proof = UploadedFile::fake()->createWithContent(
            'proof.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO7+Q7YAAAAASUVORK5CYII=')
        );

        $response = $this->actingAs($courier)->post(route('courier.deliver', $order), [
            'proof_of_delivery' => $proof,
        ]);

        $response->assertRedirect(route('courier.dashboard'));

        $order->refresh();

        $this->assertSame('delivered', $order->status);
        $this->assertNotNull($order->pod_image_path);
        $this->assertNotNull($order->pod_image_mime);
        $this->assertNotNull($order->pod_image_size);
        $this->assertNotNull($order->pod_uploaded_at);

        Storage::disk('public')->assertExists($order->pod_image_path);
    }

    public function test_delivery_proof_must_be_an_image_when_provided(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'user']);
        $courier = User::factory()->create(['role' => 'courier']);

        $order = DeliveryOrder::create([
            'user_id' => $user->id,
            'courier_id' => $courier->id,
            'status' => 'at_dropoff',
            'product_description' => 'Documents',
            'estimated_weight' => 0.50,
            'pickup_address' => 'Pickup',
            'delivery_address' => 'Dropoff',
        ]);

        $notImage = UploadedFile::fake()->create('proof.pdf', 100, 'application/pdf');

        $response = $this->actingAs($courier)
            ->from(route('courier.dashboard'))
            ->post(route('courier.deliver', $order), [
                'proof_of_delivery' => $notImage,
            ]);

        $response->assertRedirect(route('courier.dashboard'));
        $response->assertSessionHasErrors('proof_of_delivery');

        $order->refresh();
        $this->assertSame('at_dropoff', $order->status);
        $this->assertNull($order->pod_image_path);
    }
}
