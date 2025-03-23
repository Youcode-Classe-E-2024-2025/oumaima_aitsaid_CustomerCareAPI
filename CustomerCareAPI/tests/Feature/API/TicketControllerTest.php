<?php

namespace Tests\Feature\API;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_create_ticket()
    {
        $user = User::factory()->create(['role' => 'client']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $ticketData = [
            'title' => 'Test Ticket',
            'description' => 'This is a test ticket',
            'priority' => 'high',
            'category' => 'technical'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/tickets', $ticketData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'ticket' => ['id', 'title', 'description', 'status', 'priority', 'category', 'user_id']
                 ]);

        $this->assertDatabaseHas('tickets', [
            'title' => $ticketData['title'],
            'user_id' => $user->id
        ]);
    }

    public function test_client_can_view_own_tickets()
    {
        $user = User::factory()->create(['role' => 'client']);
        $token = $user->createToken('auth_token')->plainTextToken;

        Ticket::factory()->count(3)->create(['user_id' => $user->id]);
        
        // Create tickets for another user
        $anotherUser = User::factory()->create();
        Ticket::factory()->count(2)->create(['user_id' => $anotherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/tickets');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'tickets' => [
                         'data' => [
                             '*' => ['id', 'title', 'description', 'status', 'priority', 'category', 'user_id']
                         ]
                     ]
                 ]);
        
        // Client should only see their own tickets
        $this->assertEquals(3, count($response->json('tickets.data')));
    }

    public function test_admin_can_view_all_tickets()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('auth_token')->plainTextToken;

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        Ticket::factory()->count(2)->create(['user_id' => $user1->id]);
        Ticket::factory()->count(3)->create(['user_id' => $user2->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/tickets');

        $response->assertStatus(200);
        
        // Admin should see all tickets
        $this->assertEquals(5, count($response->json('tickets.data')));
    }

    public function test_client_can_view_own_ticket_details()
    {
        $user = User::factory()->create(['role' => 'client']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/tickets/' . $ticket->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'ticket' => [
                         'id' => $ticket->id,
                         'title' => $ticket->title,
                         'user_id' => $user->id
                     ]
                 ]);
    }

    public function test_client_cannot_view_other_users_ticket()
    {
        $user = User::factory()->create(['role' => 'client']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $anotherUser = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $anotherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/tickets/' . $ticket->id);

        $response->assertStatus(404);
    }

    public function test_client_can_update_own_ticket()
    {
        $user = User::factory()->create(['role' => 'client']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated Description'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/tickets/' . $ticket->id, $updateData);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Ticket updated successfully',
                     'ticket' => [
                         'id' => $ticket->id,
                         'title' => $updateData['title'],
                         'description' => $updateData['description']
                     ]
                 ]);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'title' => $updateData['title']
        ]);
    }

    public function test_client_cannot_update_other_users_ticket()
    {
        $user = User::factory()->create(['role' => 'client']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $anotherUser = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $anotherUser->id]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated Description'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/tickets/' . $ticket->id, $updateData);

        $response->assertStatus(404);
    }

    public function test_client_can_delete_own_ticket()
    {
        $user = User::factory()->create(['role' => 'client']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/tickets/' . $ticket->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Ticket deleted successfully'
                 ]);

        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
    }

    public function test_admin_can_assign_ticket_to_agent()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('auth_token')->plainTextToken;

        $agent = User::factory()->create(['role' => 'agent']);
        $ticket = Ticket::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/tickets/' . $ticket->id . '/assign', [
            'agent_id' => $agent->id
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Ticket assigned successfully',
                     'ticket' => [
                         'id' => $ticket->id,
                         'agent_id' => $agent->id
                     ]
                 ]);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'agent_id' => $agent->id
        ]);
    }

    public function test_admin_can_change_ticket_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('auth_token')->plainTextToken;

        $ticket = Ticket::factory()->create(['status' => 'open']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/tickets/' . $ticket->id . '/status', [
            'status' => 'resolved'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Ticket status updated successfully',
                     'ticket' => [
                         'id' => $ticket->id,
                         'status' => 'resolved'
                     ]
                 ]);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'resolved'
        ]);
    }
}
