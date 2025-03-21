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
 /**
     * @OA\Get(
     *     path="/api/tickets",
     *     summary="Get all tickets",
     *     tags={"Tickets"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"open", "in_progress", "resolved", "closed"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filter by priority",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"low", "medium", "high", "urgent"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search in title and description",
     *         required=false,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort_field",
     *         in="query",
     *         description="Field to sort by",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"created_at", "updated_at", "title", "status", "priority"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Direction to sort by",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"asc", "desc"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=10
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of tickets",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="first_page_url", type="string"),
     *             @OA\Property(property="from", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="last_page_url", type="string"),
     *             @OA\Property(property="links", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="next_page_url", type="string"),
     *             @OA\Property(property="path", type="string"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="prev_page_url", type="string"),
     *             @OA\Property(property="to", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     )
     * )
     */
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