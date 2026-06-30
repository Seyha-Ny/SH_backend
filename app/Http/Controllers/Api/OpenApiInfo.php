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
 *     schema="Product",
 *     required={"id", "name", "price"},
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="price", type="number", format="float"),
 *     @OA\Property(property="stock", type="integer"),
 *     @OA\Property(property="category_id", type="integer")
 * )
 */
class OpenApiInfo
{
}
