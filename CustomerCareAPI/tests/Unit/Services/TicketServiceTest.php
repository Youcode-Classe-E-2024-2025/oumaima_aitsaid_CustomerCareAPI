<?php

namespace Tests\Unit\Services;

use App\Models\Ticket;
use App\Models\User;
use App\Services\Implementations\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $ticketService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ticketService = new TicketService();
    }

    public function test_create_ticket()
    {
        $user = User::factory()->create(['role' => 'client']);
        $this->actingAs($user);

        $ticketData = [
            'title' => 'Test Ticket',
            'description' => 'This is a test ticket',
            'priority' => 'high',
            'category' => 'technical'
        ];

        $ticket = $this->ticketService->createTicket($ticketData);

        $this->assertEquals($ticketData['title'], $ticket->title);
        $this->assertEquals($ticketData['description'], $ticket->description);
        $this->assertEquals($ticketData['priority'], $ticket->priority);
        $this->assertEquals($ticketData['category'], $ticket->category);
        $this->assertEquals('open', $ticket->status);
        $this->assertEquals($user->id, $ticket->user_id);
        $this->assertDatabaseHas('tickets', ['title' => $ticketData['title']]);
    }

    public function test_get_ticket_by_id()
    {
        $user = User::factory()->create(['role' => 'client']);
        $this->actingAs($user);

        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $result = $this->ticketService->getTicketById($ticket->id);

        $this->assertEquals($ticket->id, $result->id);
        $this->assertEquals($ticket->title, $result->title);
    }

    public function test_client_cannot_access_other_users_ticket()
    {
        $user1 = User::factory()->create(['role' => 'client']);
        $user2 = User::factory()->create(['role' => 'client']);
        $this->actingAs($user1);

        $ticket = Ticket::factory()->create(['user_id' => $user2->id]);

        $result = $this->ticketService->getTicketById($ticket->id);

        $this->assertNull($result);
    }

    public function test_admin_can_access_any_ticket()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'client']);
        $this->actingAs($admin);

        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $result = $this->ticketService->getTicketById($ticket->id);

        $this->assertEquals($ticket->id, $result->id);
    }

    public function test_update_ticket()
    {
        $user = User::factory()->create(['role' => 'client']);
        $this->actingAs($user);

        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated Description'
        ];

        $result = $this->ticketService->updateTicket($ticket->id, $updateData);

        $this->assertEquals($updateData['title'], $result->title);
        $this->assertEquals($updateData['description'], $result->description);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'title' => $updateData['title']]);
    }

    public function test_delete_ticket()
    {
        $user = User::factory()->create(['role' => 'client']);
        $this->actingAs($user);

        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $result = $this->ticketService->deleteTicket($ticket->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
    }

    public function test_assign_ticket()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $agent = User::factory()->create(['role' => 'agent']);
        $this->actingAs($admin);

        $ticket = Ticket::factory()->create();

        $result = $this->ticketService->assignTicket($ticket->id, $agent->id);

        $this->assertEquals($agent->id, $result->agent_id);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'agent_id' => $agent->id]);
    }

    public function test_change_ticket_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $ticket = Ticket::factory()->create(['status' => 'open']);

        $result = $this->ticketService->changeTicketStatus($ticket->id, 'resolved');

        $this->assertEquals('resolved', $result->status);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'resolved']);
    }
}
