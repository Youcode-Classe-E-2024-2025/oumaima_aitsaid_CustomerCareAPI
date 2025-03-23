<?php

namespace Tests\Feature\API;

use App\Models\Response;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResponseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_get_responses_for_own_ticket()
    {
        $user = User::factory()->create(['role' => 'client']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $ticket = Ticket::factory()->create(['user_id' => $user->id]);
        Response::factory()->count(3)->create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'is_private' => false
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/tickets/' . $ticket->id . '/responses');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'responses' => [
                         '*' => ['id', 'content', 'ticket_id', 'user_id', 'is_private', 'created_at', 'updated_at', 'user']
                     ]
                 ]);
        
        $this->assertCount(3, $response->json('responses'));
    }

    public function test_client_cannot_see_private_responses()
    {
        $user = User::factory()->create(['role' => 'client']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $ticket = Ticket::factory()->create(['user_id' => $user->id]);
        
        // Create 2 public and 1 private response
        Response::factory()->count(2)->create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'is_private' => false
        ]);
        
        Response::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'is_private' => true
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/tickets/' . $ticket->id . '/responses');

        $response->assertStatus(200);
        
        // Client should only see public responses
        $this->assertCount(2, $response->json('responses'));
    }

    public function test_agent_can_see_private_responses()
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $token = $agent->createToken('auth_token')->plainTextToken;

        $ticket = Ticket::factory()->create(['agent_id' => $agent->id]);
        
        // Create 2 public and 1 private response
        Response::factory()->count(2)->create([
            'ticket_id' => $ticket->id,
            'user_id' => $agent->id,
            'is_private' => false
        ]);
        
        Response::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $agent->id,
            'is_private' => true
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/tickets/' . $ticket->id . '/responses');

        $response->assertStatus(200);
        
        // Agent should see all responses including private ones
        $this->assertCount(3, $response->json('responses'));
    }

    public function test_client_can_create_response_for_own_ticket()
    {
        $user = User::factory()->create(['role' => 'client']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $responseData = [
            'content' => 'This is a test response'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/tickets/' . $ticket->id . '/responses', $responseData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'response' => ['id', 'content', 'ticket_id', 'user_id', 'is_private', 'created_at', 'updated_at', 'user']
                 ]);

        $this->assertDatabaseHas('responses', [
            'content' => $responseData['content'],
            'ticket_id' => $ticket->id,
            'user_id' => $user->id
        ]);
    }

    public function test_client_cannot_create_private_response()
    {
        $user = User::factory()->create(['role' => 'client']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $responseData = [
            'content' => 'This is a test response',
            'is_private' => true
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/tickets/' . $ticket->id . '/responses', $responseData);

        $response->assertStatus(201);
        
        // Even though client requested private, it should be saved as public
        $this->assertFalse($response->json('response.is_private'));
    }

    public function test_agent_can_create_private_response()
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $token = $agent->createToken('auth_token')->plainTextToken;

        $ticket = Ticket::factory()->create(['agent_id' => $agent->id]);

        $responseData = [
            'content' => 'This is a private response',
            'is_private' => true
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/tickets/' . $ticket->id . '/responses', $responseData);

        $response->assertStatus(201);
        
        // Agent should be able to create private response
        $this->assertTrue($response->json('response.is_private'));
    }

    public function test_client_can_update_own_response()
    {
        $user = User::factory()->create(['role' => 'client']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $ticket = Ticket::factory()->create(['user_id' => $user->id]);
        $ticketResponse = Response::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'content' => 'Original content'
        ]);

        $updateData = [
            'content' => 'Updated content'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/responses/' . $ticketResponse->id, $updateData);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Response updated successfully',
                     'response' => [
                         'id' => $ticketResponse->id,
                         'content' => $updateData['content']
                     ]
                 ]);

        $this->assertDatabaseHas('responses', [
            'id' => $ticketResponse->id,
            'content' => $updateData['content']
        ]);
    }

    public function test_client_cannot_update_others_response()
    {
        $user = User::factory()->create(['role' => 'client']);
        $anotherUser = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $ticket = Ticket::factory()->create(['user_id' => $user->id]);
        $ticketResponse = Response::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $anotherUser->id,
            'content' => 'Original content'
        ]);

        $updateData = [
            'content' => 'Updated content'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson('/api/responses/' . $ticketResponse->id, $updateData);

        $response->assertStatus(404);
    }

    public function test_client_can_delete_own_response()
    {
        $user = User::factory()->create(['role' => 'client']);
        $token = $user->createToken('auth_token')->plainTextToken;

        $ticket = Ticket::factory()->create(['user_id' => $user->id]);
        $ticketResponse = Response::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/responses/' . $ticketResponse->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Response deleted successfully'
                 ]);

        $this->assertDatabaseMissing('responses', ['id' => $ticketResponse->id]);
    }

    public function test_client_cannot_delete_others_response()
    {
        $user = User::factory()->create(['role' => 'client']);
        $anotherUser = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $ticket = Ticket::factory()->create(['user_id' => $user->id]);
        $ticketResponse = Response::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $anotherUser->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/responses/' . $ticketResponse->id);

        $response->assertStatus(404);
    }

    public function test_admin_can_delete_any_response()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'client']);
        $token = $admin->createToken('auth_token')->plainTextToken;

        $ticket = Ticket::factory()->create(['user_id' => $user->id]);
        $ticketResponse = Response::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson('/api/responses/' . $ticketResponse->id);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Response deleted successfully'
                 ]);

        $this->assertDatabaseMissing('responses', ['id' => $ticketResponse->id]);
    }
}
