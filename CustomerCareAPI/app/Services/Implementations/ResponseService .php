<?php

namespace App\Services\Implementations;

use App\Models\Response;
use App\Models\Ticket;
use App\Services\Interfaces\ResponseServiceInterface;
use Illuminate\Support\Facades\Auth;

class ResponseService implements ResponseServiceInterface
{
    public function getTicketResponses($ticketId)
    {
        $user = Auth::user();
        $ticket = Ticket::find($ticketId);
        
        if (!$ticket) {
            return null;
        }
        
        // Check if user has access to this ticket
        if ($user->role === 'client' && $ticket->user_id !== $user->id) {
            return null;
        }
        
        $query = Response::with('user')->where('ticket_id', $ticketId);
        
        // Clients should not see private responses
        if ($user->role === 'client') {
            $query->where('is_private', false);
        }
        
        return $query->orderBy('created_at', 'asc')->get();
    }
    
    public function getResponseById($id)
    {
        $user = Auth::user();
        $response = Response::with('user')->find($id);
        
        if (!$response) {
            return null;
        }
        
        $ticket = $response->ticket;
        
        // Check if user has access to this ticket
        if ($user->role === 'client' && $ticket->user_id !== $user->id) {
            return null;
        }
        
        // Clients should not see private responses
        if ($user->role === 'client' && $response->is_private) {
            return null;
        }
        
        return $response;
    }
    
    public function createResponse(array $data)
    {
        $user = Auth::user();
        $ticket = Ticket::find($data['ticket_id']);
        
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
        
        // Only agents and admins can create private responses
        if (isset($data['is_private']) && $data['is_private'] && $user->role === 'client') {
            $data['is_private'] = false;
        }
        
        $responseData = [
            'content' => $data['content'],
            'ticket_id' => $data['ticket_id'],
            'user_id' => $user->id,
            'is_private' => $data['is_private'] ?? false,
        ];
        
        $response = Response::create($responseData);
        
        // Update ticket status if needed
        if ($user->role === 'client' && $ticket->status === 'resolved') {
            $ticket->status = 'open';
            $ticket->save();
        }
        
        return $response->load('user');
    }
    
    public function updateResponse($id, array $data)
    {
        $user = Auth::user();
        $response = Response::find($id);
        
        if (!$response) {
            return null;
        }
        
        // Only the creator or admin can update a response
        if ($user->role !== 'admin' && $response->user_id !== $user->id) {
            return null;
        }
        
        // Only agents and admins can update to private
        if (isset($data['is_private']) && $data['is_private'] && $user->role === 'client') {
            $data['is_private'] = false;
        }
        
        $response->update($data);
        return $response->fresh()->load('user');
    }
    
    public function deleteResponse($id)
    {
        $user = Auth::user();
        $response = Response::find($id);
        
        if (!$response) {
            return false;
        }
        
        // Only the creator or admin can delete a response
        if ($user->role !== 'admin' && $response->user_id !== $user->id) {
            return false;
        }
        
        return $response->delete();
    }
}