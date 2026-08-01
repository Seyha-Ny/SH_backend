<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/products/{product}/recommendations",
 *     summary="Get product recommendations",
 *     tags={"Recommendations"},
 *     @OA\Parameter(
 *         name="product",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful response",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Product Name"),
 *                 @OA\Property(property="price", type="number", format="float", example=99.99),
 *                 @OA\Property(property="category_id", type="integer", example=1)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Product not found"
 *     )
 * )
 */
class RecommendationController extends Controller
{
    public function forProduct(Request $request, Product $product): JsonResponse
    {
        $recommendations = Cache::remember("recommendations.product.{$product->id}", now()->addHours(4), function () use ($product) {
            return Product::where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->inRandomOrder()
                ->limit(8)
                ->get();
        });

        return response()->json($recommendations);
    }

    /**
     * @OA\Get(
 *     path="/api/recommendations",
 *     summary="Get personalized recommendations for user",
 *     tags={"Recommendations"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Successful response",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Product Name"),
 *                 @OA\Property(property="price", type="number", format="float", example=99.99),
 *                 @OA\Property(property="category_id", type="integer", example=1)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated"
 *     )
 * )
     */
    public function forUser(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        if (! $user) {
            return response()->json([]);
        }

        $userId = $user->id;

        $recommendations = Cache::remember("recommendations.user.{$userId}", now()->addHours(6), function () use ($request) {
            $user = $request->user('sanctum');

            if (! $user) {
                return collect();
            }

            $orderedProductIds = $user->orders()
                ->with('items.product')
                ->get()
                ->pluck('items.*.product_id')
                ->flatten()
                ->unique()
                ->toArray();

            $wishlistProductIds = $user->wishlists()
                ->pluck('product_id')
                ->toArray();

            $categoryIds = Product::whereIn('id', array_merge($orderedProductIds, $wishlistProductIds))
                ->pluck('category_id')
                ->unique();

            if ($categoryIds->isEmpty()) {
                return Product::inRandomOrder()->limit(8)->get();
            }

            return Product::whereIn('category_id', $categoryIds)
                ->whereNotIn('id', array_merge($orderedProductIds, $wishlistProductIds))
                ->inRandomOrder()
                ->limit(8)
                ->get();
        });

        return response()->json($recommendations);
    }
}
