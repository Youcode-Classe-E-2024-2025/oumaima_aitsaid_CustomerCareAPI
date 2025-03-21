<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\ResponseServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResponseController extends Controller
{
    protected $responseService;

    public function __construct(ResponseServiceInterface $responseService)
    {
        $this->responseService = $responseService;
    }
/**
     * @OA\Get(
     *     path="/api/tickets/{ticketId}/responses",
     *     summary="Get all responses for a ticket",
     *     tags={"Responses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="ticketId",
     *         in="path",
     *         description="Ticket ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of responses",
     *         @OA\JsonContent(
     *             @OA\Property(property="responses", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ticket not found or access denied",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ticket not found or access denied")
     *         )
     *     )
     * )
     */
    public function index($ticketId)
    {
        $responses = $this->responseService->getResponsesByTicketId($ticketId);
        
        if ($responses === null) {
            return response()->json(['message' => 'Ticket not found or access denied'], 404);
        }
        
        return response()->json(['responses' => $responses]);
    }

    public function store(Request $request, $ticketId)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['ticket_id'] = $ticketId;
        
        $response = $this->responseService->createResponse($data);
        
        if (!$response) {
            return response()->json(['message' => 'Ticket not found or access denied'], 404);
        }
        
        return response()->json([
            'message' => 'Response created successfully',
            'response' => $response
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $response = $this->responseService->updateResponse($id, $request->all());
        
        if (!$response) {
            return response()->json(['message' => 'Response not found or access denied'], 404);
        }
        
        return response()->json([
            'message' => 'Response updated successfully',
            'response' => $response
        ]);
    }

    public function destroy($id)
    {
        $result = $this->responseService->deleteResponse($id);
        
        if (!$result) {
            return response()->json(['message' => 'Response not found or access denied'], 404);
        }
        
        return response()->json(['message' => 'Response deleted successfully']);
    }
}
