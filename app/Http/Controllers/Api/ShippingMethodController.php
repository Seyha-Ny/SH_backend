<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use OpenApi\Annotations as OA;

class ShippingMethodController extends Controller
{
    /**
     * @OA\Get(
 *     path=\"/api/shipping-methods\",
 *     summary=\"Get available shipping methods\",
 *     tags={\"Shipping\"},
 *     @OA\Response(
 *         response=200,
 *         description=\"Successful response\",
 *         @OA\JsonContent(
 *             type=\"array\",
 *             @OA\Items(
 *                 @OA\Property(property=\"id\", type=\"integer\", example=1),
 *                 @OA\Property(property=\"name\", type=\"string\", example=\"Standard Shipping\"),
 *                 @OA\Property(property=\"code\", type=\"string\", example=\"standard\"),
 *                 @OA\Property(property=\"courier\", type=\"string\", example=\"UPS\"),
 *                 @OA\Property(property=\"fee\", type=\"number\", format=\"float\", example=9.99),
 *                 @OA\Property(property=\"estimated_days_min\", type=\"integer\", example=3),
 *                 @OA\Property(property=\"estimated_days_max\", type=\"integer\", example=7)
 *             )
 *         )
 *     )
 * )
     */
    public function index(): JsonResponse
    {
        $methods = Cache::remember('shipping_methods.active', now()->addHours(6), function () {
            return ShippingMethod::query()
                ->where('active', true)
                ->orderBy('fee')
                ->get(['id', 'name', 'code', 'courier', 'fee', 'estimated_days_min', 'estimated_days_max']);
        });

        return response()->json($methods);
    }
}
