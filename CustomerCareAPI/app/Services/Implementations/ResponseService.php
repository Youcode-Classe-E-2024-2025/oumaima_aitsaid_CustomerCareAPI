<?php

namespace App\Services\Implementations;

use App\Models\Response;
use App\Models\Ticket;
use App\Services\Interfaces\ResponseServiceInterface;
use Illuminate\Support\Facades\Auth;

class ResponseService implements ResponseServiceInterface
{
    public function getResponsesByTicketId(int $ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        
        // Check if user has access to this ticket
        $user = Auth::user();
        if ($user->role === 'client' && $ticket->user_id !== $user->id) {
            return null;
        }

        return Response::with('user')
            ->where('ticket_id', $ticketId)
            ->orderBy('created_at')
            ->get();
    }

    public function createResponse(array $data)
    {
        $ticket = Ticket::findOrFail($data['ticket_id']);
        
        // Check if user has access to this ticket
        $user = Auth::user();
        if ($user->role === 'client' && $ticket->user_id !== $user->id) {
            return null;
        }

        $data['user_id'] = Auth::id();
        return Response::create($data);
    }

    public function updateResponse(int $id, array $data)
    {
        $response = Response::findOrFail($id);
        
        // Check if user is the owner of the response
        if ($response->user_id !== Auth::id()) {
            return null;
        }

        $response->update($data);
        return $response;
    }

    public function deleteResponse(int $id)
    {
        $response = Response::findOrFail($id);
        
        // Check if user is the owner of the response or an admin
        $user = Auth::user();
        if ($response->user_id !== $user->id && !$user->isAdmin()) {
            return false;
        }

        return $response->delete();
    }
}