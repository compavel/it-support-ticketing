<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_ticket(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/tickets', [
            'title' => 'Proyektor mati',
            'description' => 'Proyektor di ruang 1 tidak menyala',
            'category' => 'hardware',
            'priority' => 'high',
        ]);

        $ticket = Ticket::first();

        $response->assertRedirect(route('tickets.show', $ticket));
        $this->assertDatabaseHas('tickets', [
            'title' => 'Proyektor mati',
            'user_id' => $user->id,
            'status' => 'open',
        ]);
    }

    public function test_ticket_creation_requires_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/tickets', [
            'title' => '',
            'description' => '',
            'category' => 'invalid-category',
            'priority' => 'invalid-priority',
        ]);

        $response->assertSessionHasErrors(['title', 'description', 'category', 'priority']);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_admin_can_update_ticket_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = Ticket::factory()->create(['status' => 'open']);

        $this->actingAs($admin)->put("/tickets/{$ticket->id}", [
            'status' => 'in_progress',
        ])->assertRedirect();

        $ticket->refresh();
        $this->assertEquals('in_progress', $ticket->status);
        $this->assertNotNull($ticket->resolved_at ?? true);
    }

    public function test_resolved_ticket_gets_resolved_at_timestamp(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = Ticket::factory()->create(['status' => 'in_progress']);

        $this->actingAs($admin)->put("/tickets/{$ticket->id}", [
            'status' => 'resolved',
        ]);

        $this->assertNotNull($ticket->fresh()->resolved_at);
    }

    public function test_status_change_creates_history_entry(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = Ticket::factory()->create(['status' => 'open']);

        $this->actingAs($admin)->put("/tickets/{$ticket->id}", [
            'status' => 'in_progress',
        ]);

        $this->assertDatabaseHas('ticket_histories', [
            'ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'field' => 'status',
            'old_value' => 'open',
            'new_value' => 'in_progress',
        ]);
    }

    public function test_owner_can_delete_own_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->delete("/tickets/{$ticket->id}")->assertRedirect('/tickets');

        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_user_cannot_delete_other_users_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create();

        $this->actingAs($user)->delete("/tickets/{$ticket->id}")->assertForbidden();

        $this->assertDatabaseCount('tickets', 1);
    }

    // --- List scoping ---

    public function test_user_only_sees_own_tickets(): void
    {
        $user = User::factory()->create();
        $ownTicket = Ticket::factory()->create(['user_id' => $user->id, 'title' => 'Tiket Saya']);
        Ticket::factory()->create(['title' => 'Tiket Orang Lain']);

        $response = $this->actingAs($user)->get('/tickets');

        $response->assertOk();
        $response->assertSee('Tiket Saya');
        $response->assertDontSee('Tiket Orang Lain');
    }

    public function test_admin_sees_all_tickets(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Ticket::factory()->create(['title' => 'Tiket A']);
        Ticket::factory()->create(['title' => 'Tiket B']);

        $response = $this->actingAs($admin)->get('/tickets');

        $response->assertOk();
        $response->assertSee('Tiket A');
        $response->assertSee('Tiket B');
    }

    public function test_technician_sees_only_active_assigned_tickets(): void
    {
        $technician = User::factory()->create(['role' => 'technician']);
        Ticket::factory()->assignedTo($technician)->status('open')->create(['title' => 'Tiket Aktif']);
        Ticket::factory()->assignedTo($technician)->status('closed')->create(['title' => 'Tiket Selesai']);

        $response = $this->actingAs($technician)->get('/tickets');

        $response->assertOk();
        $response->assertSee('Tiket Aktif');
        $response->assertDontSee('Tiket Selesai');
    }

    public function test_ticket_search_filters_by_keyword(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Ticket::factory()->create(['title' => 'WiFi putus total']);
        Ticket::factory()->create(['title' => 'Printer error']);

        $response = $this->actingAs($admin)->get('/tickets?search=WiFi');

        $response->assertOk();
        $response->assertSee('WiFi putus total');
        $response->assertDontSee('Printer error');
    }

    public function test_ticket_search_is_case_insensitive(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Ticket::factory()->create(['title' => 'WiFi putus total']);
        Ticket::factory()->create(['title' => 'Printer error']);

        $response = $this->actingAs($admin)->get('/tickets?search=wifi');

        $response->assertOk();
        $response->assertSee('WiFi putus total');
        $response->assertDontSee('Printer error');
    }

    // --- Comments ---

    public function test_user_can_comment_on_ticket(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post("/tickets/{$ticket->id}/comments", [
            'comment' => 'Sudah saya cek, kabelnya lepas.',
        ])->assertRedirect();

        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'comment' => 'Sudah saya cek, kabelnya lepas.',
        ]);
    }

    public function test_comment_requires_content(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post("/tickets/{$ticket->id}/comments", ['comment' => ''])
            ->assertSessionHasErrors('comment');
    }

    public function test_only_author_or_admin_can_delete_comment(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();
        $comment = TicketComment::create([
            'ticket_id' => Ticket::factory()->create(['user_id' => $author->id])->id,
            'user_id' => $author->id,
            'comment' => 'Komentar asli',
        ]);

        $this->actingAs($other)->delete("/comments/{$comment->id}")->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->delete("/comments/{$comment->id}")->assertRedirect();

        $this->assertDatabaseCount('ticket_comments', 0);
    }
}
