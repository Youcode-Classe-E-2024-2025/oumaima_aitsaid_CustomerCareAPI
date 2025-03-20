<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ResponseController;
use App\Http\Controllers\API\TicketController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Ticket routes
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/tickets/{id}', [TicketController::class, 'show']);
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::put('/tickets/{id}', [TicketController::class, 'update']);
    Route::delete('/tickets/{id}', [TicketController::class, 'destroy'])->middleware('role:admin');
    Route::post('/tickets/{id}/assign', [TicketController::class, 'assign'])->middleware('role:agent,admin');
    Route::post('/tickets/{id}/status', [TicketController::class, 'changeStatus']);
    
    // Response routes
    Route::get('/tickets/{ticketId}/responses', [ResponseController::class, 'index']);
    Route::post('/tickets/{ticketId}/responses', [ResponseController::class, 'store']);
    Route::put('/responses/{id}', [ResponseController::class, 'update']);
    Route::delete('/responses/{id}', [ResponseController::class, 'destroy']);
});
