<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_product_management_routes(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $product = Product::create([
            'name' => 'Sample Product',
            'detail' => 'Sample detail',
            'who' => 'Owner',
            'warehouse' => 'Main Warehouse',
            'courier_name' => 'Courier One',
        ]);

        $this->actingAs($user)->get(route('products.index'))->assertForbidden();
        $this->actingAs($user)->get(route('products.create'))->assertForbidden();
        $this->actingAs($user)->post(route('products.store'), [
            'name' => 'Unauthorized',
            'detail' => 'Attempt',
            'who' => 'U',
            'warehouse' => 'W',
            'courier_name' => 'C',
        ])->assertForbidden();

        $this->actingAs($user)->get(route('products.edit', $product))->assertForbidden();
        $this->actingAs($user)->put(route('products.update', $product), [
            'name' => 'Updated',
            'detail' => 'Updated detail',
            'who' => 'Owner',
            'warehouse' => 'Main Warehouse',
            'courier_name' => 'Courier One',
        ])->assertForbidden();
        $this->actingAs($user)->delete(route('products.destroy', $product))->assertForbidden();
    }

    public function test_admin_product_store_requires_expected_field_constraints(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('products.create'))
            ->post(route('products.store'), [
                'name' => str_repeat('A', 256),
                'detail' => 'Valid detail',
                'who' => 'Owner',
                'warehouse' => 'Main Warehouse',
                'courier_name' => 'Courier One',
            ])
            ->assertRedirect(route('products.create'))
            ->assertSessionHasErrors(['name']);

        $this->assertDatabaseCount('products', 0);
    }

    public function test_admin_product_store_and_update_use_validated_payload(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('products.store'), [
                'name' => 'Product A',
                'detail' => 'Detail A',
                'who' => 'Owner A',
                'warehouse' => 'Warehouse A',
                'courier_name' => 'Courier A',
                'role' => 'admin',
            ])
            ->assertRedirect(route('products.index'));

        $product = Product::firstOrFail();

        $this->actingAs($admin)
            ->put(route('products.update', $product), [
                'name' => 'Product A Updated',
                'detail' => 'Detail Updated',
                'who' => 'Owner Updated',
                'warehouse' => 'Warehouse Updated',
                'courier_name' => 'Courier Updated',
                'unexpected' => 'value',
            ])
            ->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Product A Updated',
            'detail' => 'Detail Updated',
            'who' => 'Owner Updated',
            'warehouse' => 'Warehouse Updated',
            'courier_name' => 'Courier Updated',
        ]);
    }
}
