<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\TicketServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    protected $ticketService;

    public function __construct(TicketServiceInterface $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    public function index(Request $request)
    {
        $filters = $request->only([
            'status', 'priority', 'search', 'sort_field', 
            'sort_direction', 'per_page'
        ]);
        
        $tickets = $this->ticketService->getAllTickets($filters);
        
        return response()->json($tickets);
    }

    public function show($id)
    {
        $ticket = $this->ticketService->getTicketById($id);
        
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found or access denied'], 404);
        }
        
        return response()->json(['ticket' => $ticket]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'sometimes|string|in:low,medium,high,urgent',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ticket = $this->ticketService->createTicket($request->all());
        
        return response()->json([
            'message' => 'Ticket created successfully',
            'ticket' => $ticket
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'priority' => 'sometimes|string|in:low,medium,high,urgent',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ticket = $this->ticketService->updateTicket($id, $request->all());
        
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found or access denied'], 404);
        }
        
        return response()->json([
            'message' => 'Ticket updated successfully',
            'ticket' => $ticket
        ]);
    }

    public function destroy($id)
    {
        $result = $this->ticketService->deleteTicket($id);
        
        if (!$result) {
            return response()->json(['message' => 'Ticket not found or access denied'], 404);
        }
        
        return response()->json(['message' => 'Ticket deleted successfully']);
    }

    public function assign(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ticket = $this->ticketService->assignTicket($id, $request->agent_id);
        
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found or access denied'], 404);
        }
        
        return response()->json([
            'message' => 'Ticket assigned successfully',
            'ticket' => $ticket
        ]);
    }

    public function changeStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:open,in_progress,resolved,closed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ticket = $this->ticketService->changeStatus($id, $request->status);
        
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found or access denied'], 404);
        }
        
        return response()->json([
            'message' => 'Ticket status changed successfully',
            'ticket' => $ticket
        ]);
    }
}