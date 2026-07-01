<?php

namespace App;

use OpenApi\Annotations as OA;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         title="Ecommerce API",
 *         version="1.0.0",
 *         description="Ecommerce Backend API Documentation"
 *     ),
 *     @OA\Server(
 *         url="http://localhost:8000",
 *         description="Local development server"
 *     ),
 *     @OA\Components(
 *         @OA\SecurityScheme(
 *             securityScheme="sanctum",
 *             type="apiKey",
 *             in="header",
 *             name="Authorization"
 *         )
 *     )
 * )
 */
class OpenApi
{
}
