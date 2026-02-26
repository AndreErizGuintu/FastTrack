<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_courier_routes(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('courier.dashboard'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('courier.profile'))
            ->assertForbidden();
    }

    public function test_courier_cannot_access_user_routes(): void
    {
        $courier = User::factory()->create(['role' => 'courier']);

        $this->actingAs($courier)
            ->get(route('user.dashboard'))
            ->assertForbidden();

        $this->actingAs($courier)
            ->get(route('user.profile'))
            ->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}
