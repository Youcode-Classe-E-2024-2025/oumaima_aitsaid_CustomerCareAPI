<?php

namespace App\Services\Implementations;

use App\Models\Ticket;
use App\Services\Interfaces\TicketServiceInterface;
use Illuminate\Support\Facades\Auth;

class TicketService implements TicketServiceInterface
{
    public function getAllTickets(array $filters = [])
    {
        $query = Ticket::with(['user', 'agent', 'responses']);

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply user role based filtering
        $user = Auth::user();
        if ($user->role === 'client') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'agent') {
            $query->where(function ($q) use ($user) {
                $q->where('agent_id', $user->id)
                  ->orWhereNull('agent_id');
            });
        }

        // Apply sorting
        $sortField = $filters['sort_field'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        // Apply pagination
        $perPage = $filters['per_page'] ?? 10;
        return $query->paginate($perPage);
    }

    public function getTicketById(int $id)
    {
        $ticket = Ticket::with(['user', 'agent', 'responses.user'])->findOrFail($id);
        
        // Check if user has access to this ticket
        $user = Auth::user();
        if ($user->role === 'client' && $ticket->user_id !== $user->id) {
            return null;
        }

        return $ticket;
    }

    public function createTicket(array $data)
    {
        $data['user_id'] = Auth::id();
        return Ticket::create($data);
    }

    public function updateTicket(int $id, array $data)
    {
        $ticket = Ticket::findOrFail($id);
        
        // Check if user has permission to update
        $user = Auth::user();
        if ($user->role === 'client' && $ticket->user_id !== $user->id) {
            return null;
        }

        $ticket->update($data);
        return $ticket;
    }

    public function deleteTicket(int $id)
    {
        $ticket = Ticket::findOrFail($id);
        
        // Only admin can delete tickets
        $user = Auth::user();
        if (!$user->isAdmin()) {
            return false;
        }

        return $ticket->delete();
    }

    public function assignTicket(int $id, int $agentId)
    {
        $ticket = Ticket::findOrFail($id);
        
        // Only agents and admins can assign tickets
        $user = Auth::user();
        if (!$user->isAgent()) {
            return null;
        }

        $ticket->agent_id = $agentId;
        $ticket->status = 'in_progress';
        $ticket->save();
        
        return $ticket;
    }

    public function changeStatus(int $id, string $status)
    {
        $ticket = Ticket::findOrFail($id);
        
        // Check if user has permission to change status
        $user = Auth::user();
        if ($user->role === 'client' && $ticket->user_id !== $user->id) {
            return null;
        }

        $ticket->status = $status;
        $ticket->save();
        
        return $ticket;
    }
}
