<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardAndUserGuardrailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_contains_drilldown_links(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('admin/orders?status=delivered&amp;proof=without', false);
        $response->assertSee(route('admin.users.index', ['role' => 'courier']), false);
    }

    public function test_admin_users_index_supports_role_filter(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Main Admin']);
        User::factory()->create(['role' => 'courier', 'name' => 'Courier One']);
        User::factory()->create(['role' => 'user', 'name' => 'Regular User']);

        $response = $this->actingAs($admin)
            ->get(route('admin.users.index', ['role' => 'courier']));

        $response->assertOk();
        $response->assertSee('Courier One');
        $response->assertDontSee('Regular User');
    }

    public function test_admin_cannot_demote_self(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->from(route('admin.users.edit', $admin))
            ->put(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'user',
            ]);

        $response->assertRedirect(route('admin.users.edit', $admin));
        $response->assertSessionHas('error');

        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_system_prevents_removing_last_admin_role(): void
    {
        $mainAdmin = User::factory()->create(['role' => 'admin']);
        $targetAdmin = User::factory()->create(['role' => 'admin']);

        // Demote one admin first (allowed)
        $this->actingAs($mainAdmin)
            ->put(route('admin.users.update', $targetAdmin), [
                'name' => $targetAdmin->name,
                'email' => $targetAdmin->email,
                'role' => 'user',
            ])
            ->assertRedirect(route('admin.users.index'));

        // Try to demote remaining last admin (blocked)
        $response = $this->actingAs($mainAdmin)
            ->from(route('admin.users.edit', $mainAdmin))
            ->put(route('admin.users.update', $mainAdmin), [
                'name' => $mainAdmin->name,
                'email' => $mainAdmin->email,
                'role' => 'courier',
            ]);

        $response->assertRedirect(route('admin.users.edit', $mainAdmin));
        $response->assertSessionHas('error');

        $this->assertSame('admin', $mainAdmin->fresh()->role);
    }
}
