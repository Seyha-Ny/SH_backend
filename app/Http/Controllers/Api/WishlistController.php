<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToWishlistRequest;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/wishlists",
 *     summary="Get user's wishlist",
 *     tags={"Wishlist"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Successful response",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="user_id", type="integer", example=1),
 *                 @OA\Property(property="product_id", type="integer", example=1),
 *                 @OA\Property(property="created_at", type="string", format="date-time"),
 *                 @OA\Property(property="product", type="object")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated"
 *     )
 * )
 */
class WishlistController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/wishlists",
     *     summary="Add product to wishlist",
     *     tags={"Wishlist"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id"},
     *             @OA\Property(property="product_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Added to wishlist",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="product_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
 * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = self::resolveSanctumUser($request);
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $wishlists = $user->wishlists()->with('product')->get();
        return response()->json($wishlists);
    }

    /**
     * @OA\Delete(
     *     path="/api/wishlists/{product}",
     *     summary="Remove product from wishlist",
     *     tags={"Wishlist"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Removed from wishlist",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Removed from wishlist")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
 * )
     */
    public function store(AddToWishlistRequest $request): JsonResponse
    {
        $user = self::resolveSanctumUser($request);
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $wishlist = Wishlist::firstOrCreate([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
        ]);

        return response()->json($wishlist, 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/wishlists/{id}",
     *     summary="Remove product from wishlist by ID",
     *     tags={"Wishlist"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Removed from wishlist",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Removed from wishlist")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
 * )
     */
    public function destroy(Request $request, Product $product): JsonResponse
    {
        $user = self::resolveSanctumUser($request);
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user->wishlists()->where('product_id', $product->id)->delete();

        return response()->json(['message' => 'Removed from wishlist']);
    }

    protected static function resolveSanctumUser(Request $request): ?\App\Models\User
    {
        $token = $request->bearerToken();

        if (! is_string($token)) {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($token);

        return $accessToken?->tokenable;
    }
}
