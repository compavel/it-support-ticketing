<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    // --- Role-based route access ---

    public function test_user_cannot_access_admin_routes(): void
    {
        $user = $this->makeUser('user');

        $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($user)->get('/admin/users')->assertForbidden();
    }

    public function test_technician_cannot_access_admin_routes(): void
    {
        $technician = $this->makeUser('technician');

        $this->actingAs($technician)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($technician)->get('/admin/users')->assertForbidden();
    }

    public function test_user_cannot_access_technician_dashboard(): void
    {
        $user = $this->makeUser('user');

        $this->actingAs($user)->get('/technician/dashboard')->assertForbidden();
    }

    public function test_admin_cannot_access_technician_dashboard(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/technician/dashboard')->assertForbidden();
    }

    // --- Ticket visibility ---

    public function test_user_cannot_view_another_users_ticket(): void
    {
        $user = $this->makeUser('user');
        $ticket = Ticket::factory()->create();

        $this->actingAs($user)->get("/tickets/{$ticket->id}")->assertForbidden();
    }

    public function test_technician_cannot_view_unassigned_ticket(): void
    {
        $technician = $this->makeUser('technician');
        $ticket = Ticket::factory()->create();

        $this->actingAs($technician)->get("/tickets/{$ticket->id}")->assertForbidden();
    }

    public function test_technician_can_view_assigned_ticket(): void
    {
        $technician = $this->makeUser('technician');
        $ticket = Ticket::factory()->assignedTo($technician)->create();

        $this->actingAs($technician)->get("/tickets/{$ticket->id}")->assertOk();
    }

    public function test_user_can_view_own_ticket(): void
    {
        $user = $this->makeUser('user');
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get("/tickets/{$ticket->id}")->assertOk();
    }

    public function test_admin_can_view_any_ticket(): void
    {
        $admin = $this->makeUser('admin');
        $ticket = Ticket::factory()->create();

        $this->actingAs($admin)->get("/tickets/{$ticket->id}")->assertOk();
    }

    // --- Ticket assignment (admin only) ---

    public function test_non_admin_cannot_assign_technician(): void
    {
        $user = $this->makeUser('user');
        $technician = $this->makeUser('technician');
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post("/tickets/{$ticket->id}/assign", ['technician_id' => $technician->id])
            ->assertForbidden();

        $this->assertNull($ticket->fresh()->technician_id);
    }

    public function test_admin_can_assign_technician(): void
    {
        $admin = $this->makeUser('admin');
        $technician = $this->makeUser('technician');
        $ticket = Ticket::factory()->create();

        $this->actingAs($admin)
            ->post("/tickets/{$ticket->id}/assign", ['technician_id' => $technician->id])
            ->assertRedirect();

        $this->assertEquals($technician->id, $ticket->fresh()->technician_id);
    }

    // --- Status change (admin only) ---

    public function test_non_admin_cannot_change_ticket_status(): void
    {
        $user = $this->makeUser('user');
        $ticket = Ticket::factory()->create(['user_id' => $user->id, 'status' => 'open']);

        $this->actingAs($user)
            ->put("/tickets/{$ticket->id}", ['status' => 'closed']);

        $this->assertEquals('open', $ticket->fresh()->status);
    }

    public function test_assigned_technician_can_change_status(): void
    {
        $technician = $this->makeUser('technician');
        $ticket = Ticket::factory()->assignedTo($technician)->create(['status' => 'open']);

        $this->actingAs($technician)
            ->put("/tickets/{$ticket->id}", ['status' => 'in_progress'])
            ->assertRedirect();

        $this->assertEquals('in_progress', $ticket->fresh()->status);
    }

    public function test_unassigned_technician_cannot_change_status(): void
    {
        $technician = $this->makeUser('technician');
        $otherTechnician = $this->makeUser('technician');
        $ticket = Ticket::factory()->assignedTo($otherTechnician)->create(['status' => 'open']);

        $this->actingAs($technician)
            ->put("/tickets/{$ticket->id}", ['status' => 'closed']);

        $this->assertEquals('open', $ticket->fresh()->status);
    }

    public function test_technician_cannot_reassign_ticket(): void
    {
        $technician = $this->makeUser('technician');
        $otherTechnician = $this->makeUser('technician');
        $ticket = Ticket::factory()->assignedTo($technician)->create();

        $this->actingAs($technician)
            ->put("/tickets/{$ticket->id}", ['technician_id' => $otherTechnician->id]);

        $this->assertEquals($technician->id, $ticket->fresh()->technician_id);
    }

    public function test_cannot_assign_non_technician_user(): void
    {
        $admin = $this->makeUser('admin');
        $regularUser = $this->makeUser('user');
        $ticket = Ticket::factory()->create();

        $this->actingAs($admin)
            ->post("/tickets/{$ticket->id}/assign", ['technician_id' => $regularUser->id])
            ->assertSessionHasErrors('technician_id');

        $this->assertNull($ticket->fresh()->technician_id);
    }

    public function test_user_cannot_comment_on_another_users_ticket(): void
    {
        $user = $this->makeUser('user');
        $ticket = Ticket::factory()->create();

        $this->actingAs($user)
            ->post("/tickets/{$ticket->id}/comments", ['comment' => 'Komentar tak berwenang'])
            ->assertForbidden();

        $this->assertDatabaseCount('ticket_comments', 0);
    }

    public function test_assigned_technician_can_comment(): void
    {
        $technician = $this->makeUser('technician');
        $ticket = Ticket::factory()->assignedTo($technician)->create();

        $this->actingAs($technician)
            ->post("/tickets/{$ticket->id}/comments", ['comment' => 'Sedang diperiksa'])
            ->assertRedirect();

        $this->assertDatabaseCount('ticket_comments', 1);
    }

    // --- Inactive account ---

    public function test_inactive_user_gets_logged_out(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->actingAs($user)
            ->get('/tickets')
            ->assertRedirect('/login');
    }
}
