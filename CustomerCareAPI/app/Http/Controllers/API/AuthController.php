<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\AuthServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Info(
 *     title="CustomerCareAPI",
 *     version="1.0.0",
 *     description="API for customer support ticket management",
 *     @OA\Contact(
 *         email="contact@example.com",
 *         name="API Support"
 *     )
 * )
 * 
 */
class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'sometimes|string|in:client,agent,admin',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $result = $this->authService->register($request->all());

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $result['user'],
            'token' => $result['token']
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $result = $this->authService->login($request->only('email', 'password'));

        if (!$result) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'message' => 'User logged in successfully',
            'user' => $result['user'],
            'token' => $result['token']
        ]);
    }

    public function logout(Request $request)
    {
        $this->authService->logout();
        return response()->json(['message' => 'User logged out successfully']);
    }

    public function user(Request $request)
    {
        return response()->json(['user' => $request->user()]);
    }
}



