<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
/**
 * @OA\Info(
 *     title="Test API",
 *     version="1.0.0"
 * )
 * 
 * @OA\PathItem(
 *     path="/test"
 * )
 */
class Controller extends BaseController
{
     /**
     * @OA\Get(
     *     path="/test",
     *     summary="Test endpoint",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    use AuthorizesRequests, ValidatesRequests;
}
