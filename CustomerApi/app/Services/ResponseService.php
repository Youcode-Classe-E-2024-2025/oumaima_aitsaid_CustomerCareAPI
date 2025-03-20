<?php

namespace App\Services;

use App\Models\Response;
use App\Models\Ticket;
use App\Services\Interfaces\responseServiceInterface;
use Illuminate\Support\Facades\Auth;

class ResponseService implements responseServiceInterface
{
    public function getResponsesByTicketId(int $ticketId)
    {
        return Response::with('user')
            ->where('ticket_id', $ticketId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function createResponse(array $data)
    {
        $data['user_id'] = Auth::id();
        
        $response = Response::create($data);
        
        // Update ticket status if needed
        if (isset($data['update_status']) && $data['update_status']) {
            $ticket = Ticket::find($data['ticket_id']);
            if ($ticket) {
                $ticket->status = $data['status'] ?? 'in_progress';
                $ticket->save();
            }
        }
        
        return $response;
    }

    public function updateResponse(int $id, array $data)
    {
        $response = Response::findOrFail($id);
        $response->update($data);
        return $response;
    }

    public function deleteResponse(int $id)
    {
        $response = Response::findOrFail($id);
        return $response->delete();
    }
}
