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
        $responses = $this->responseService->getTicketResponses($ticketId);
        
        if ($responses === null) {
            return response()->json(['message' => 'Ticket not found or access denied'], 404);
        }
        
        return response()->json(['responses' => $responses]);
    }

    /**
     * @OA\Get(
     *     path="/api/responses/{id}",
     *     summary="Get a specific response",
     *     tags={"Responses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Response ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response details",
     *         @OA\JsonContent(
     *             @OA\Property(property="response", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Response not found or access denied",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Response not found or access denied")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $response = $this->responseService->getResponseById($id);
        
        if (!$response) {
            return response()->json(['message' => 'Response not found or access denied'], 404);
        }
        
        return response()->json(['response' => $response]);
    }

    /**
     * @OA\Post(
     *     path="/api/tickets/{ticketId}/responses",
     *     summary="Create a new response",
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="content", type="string", example="This is a response to the ticket"),
     *             @OA\Property(property="is_private", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Response created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Response created successfully"),
     *             @OA\Property(property="response", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Ticket not found or access denied",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Ticket not found or access denied")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request, $ticketId)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'is_private' => 'sometimes|boolean',
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

    /**
     * @OA\Put(
     *     path="/api/responses/{id}",
     *     summary="Update a response",
     *     tags={"Responses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Response ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="content", type="string", example="Updated response content"),
     *             @OA\Property(property="is_private", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Response updated successfully"),
     *             @OA\Property(property="response", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Response not found or access denied",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Response not found or access denied")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'sometimes|string',
            'is_private' => 'sometimes|boolean',
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

    /**
     * @OA\Delete(
     *     path="/api/responses/{id}",
     *     summary="Delete a response",
     *     tags={"Responses"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Response ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Response deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Response not found or access denied",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Response not found or access denied")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $result = $this->responseService->deleteResponse($id);
        
        if (!$result) {
            return response()->json(['message' => 'Response not found or access denied'], 404);
        }
        
        return response()->json(['message' => 'Response deleted successfully']);
    }
}
