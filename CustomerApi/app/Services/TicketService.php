<?php

namespace App\Services;

use App\Models\Ticket;
use App\Services\Interfaces\TicketServiceInterface;
use Illuminate\Support\Facades\Auth;

class TicketService implements TicketServiceInterface
{
    public function getAllTickets(array $filters = [], int $perPage = 15)
    {
        $query = Ticket::with(['user', 'agent']);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['agent_id'])) {
            $query->where('agent_id', $filters['agent_id']);
        }

        // Apply search
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Apply sorting
        $sortField = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($perPage);
    }

    public function getTicketById(int $id)
    {
        return Ticket::with(['user', 'agent', 'responses.user'])->findOrFail($id);
    }

    public function createTicket(array $data)
    {
        $data['user_id'] = Auth::id();
        return Ticket::create($data);
    }

    public function updateTicket(int $id, array $data)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->update($data);
        return $ticket;
    }

    public function deleteTicket(int $id)
    {
        $ticket = Ticket::findOrFail($id);
        return $ticket->delete();
    }

    public function assignTicket(int $ticketId, int $agentId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        $ticket->agent_id = $agentId;
        $ticket->status = 'in_progress';
        $ticket->save();
        return $ticket;
    }

    public function changeStatus(int $ticketId, string $status)
    {
        $ticket = Ticket::findOrFail($ticketId);
        $ticket->status = $status;
        $ticket->save();
        return $ticket;
    }
}
