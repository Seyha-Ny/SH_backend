<?php

namespace App\Http\Controllers\Api;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Ecommerce API",
 *     version="1.0.0",
 *     description="Ecommerce Backend API Documentation"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization"
 * )
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local development server"
 * )
 * 
 * @OA\Schema(
 *     schema="Error",
 *     required={"message"},
 *     @OA\Property(property="message", type="string", example="Error message")
 * )
 */
class SwaggerInfo
{
}
