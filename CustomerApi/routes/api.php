<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TicketController;
use App\Http\Controllers\API\ResponseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Tickets
    Route::apiResource('tickets', TicketController::class);
    Route::post('/tickets/{id}/assign', [TicketController::class, 'assignTicket']);
    Route::post('/tickets/{id}/status', [TicketController::class, 'changeStatus']);
    
    // Responses
    Route::get('/tickets/{ticket_id}/responses', [ResponseController::class, 'index']);
    Route::post('/tickets/{ticket_id}/responses', [ResponseController::class, 'store']);
    Route::apiResource('responses', ResponseController::class)->except(['index', 'store']);
});
