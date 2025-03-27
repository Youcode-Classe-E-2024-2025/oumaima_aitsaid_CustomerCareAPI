<?php

namespace App\Services\Implementations;

use App\Models\Ticket;
use App\Services\Interfaces\TicketServiceInterface;
use Illuminate\Support\Facades\Auth;

class TicketService implements TicketServiceInterface
{
    public function getAllTickets(array $filters)
    {
        $query = Ticket::with(['user', 'agent']);
        
        // Apply role-based filtering
        $user = Auth::user();
        if ($user->role === 'client') {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'agent') {
            $query->where(function($q) use ($user) {
                $q->where('agent_id', $user->id)
                  ->orWhereNull('agent_id');
            });
        }
        // Admin can see all tickets
        
        // Apply status filter
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        // Apply priority filter
        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        
        // Apply search filter
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Apply sorting
        $sortField = $filters['sort_field'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);
        
        // Paginate results
        $perPage = $filters['per_page'] ?? 10;
        
        return $query->paginate($perPage);
    }
    
    public function getTicketById($id)
    {
        $user = Auth::user();
        $ticket = Ticket::with(['user', 'agent'])->find($id);
        
        if (!$ticket) {
            return null;
        }
        
        // Check if user has access to this ticket
        if ($user->role === 'client' && $ticket->user_id !== $user->id) {
            return null;
        }
        
        if ($user->role === 'agent' && $ticket->agent_id !== $user->id && $ticket->agent_id !== null) {
            return null;
        }
        
        return $ticket;
    }
    
    public function createTicket(array $data)
    {
        $user = Auth::user();
        
        $ticketData = [
            'title' => $data['title'],
            'description' => $data['description'],
            'priority' => $data['priority'] ?? 'medium',
            'status' => 'open',
            'user_id' => $user->id,
        ];
        
        return Ticket::create($ticketData);
    }
    
    public function updateTicket($id, array $data)
    {
        $user = Auth::user();
        $ticket = Ticket::find($id);
        
        if (!$ticket) {
            return null;
        }
        
        // Check if user has access to update this ticket
        if ($user->role === 'client' && $ticket->user_id !== $user->id) {
            return null;
        }
        
        if ($user->role === 'agent' && $ticket->agent_id !== $user->id) {
            return null;
        }
        
        $ticket->update($data);
        return $ticket->fresh();
    }
    
    public function deleteTicket($id)
    {
        $user = Auth::user();
        $ticket = Ticket::find($id);
        
        if (!$ticket) {
            return false;
        }
        
        // Only admin or the ticket creator can delete tickets
        if ($user->role !== 'admin' && $ticket->user_id !== $user->id) {
            return false;
        }
        
        return $ticket->delete();
    }
    
    public function assignTicket($id, $agentId)
    {
        $user = Auth::user();
        $ticket = Ticket::find($id);
        
        if (!$ticket) {
            return null;
        }
        
        // Only admin can assign tickets
        if ($user->role !== 'admin') {
            return null;
        }
        
        $ticket->agent_id = $agentId;
        $ticket->status = 'in_progress';
        $ticket->save();
        
        return $ticket->fresh();
    }
    
    public function changeStatus($id, $status)
    {
        $user = Auth::user();
        $ticket = Ticket::find($id);
        
        if (!$ticket) {
            return null;
        }
        
        // Check if user has access to change status
        if ($user->role === 'client' && $ticket->user_id !== $user->id) {
            return null;
        }
        
        if ($user->role === 'agent' && $ticket->agent_id !== $user->id) {
            return null;
        }
        
        $ticket->status = $status;
        $ticket->save();
        
        return $ticket->fresh();
    }
}

