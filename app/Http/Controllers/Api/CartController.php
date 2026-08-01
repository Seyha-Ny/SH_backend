<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exceptions\UnauthenticatedException;
use App\Http\Requests\AddCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/cart",
 *     summary="Get user's cart items",
 *     tags={"Cart"},
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
 *                 @OA\Property(property="quantity", type="integer", example=2),
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
class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/cart",
     *     summary="Add item to cart",
     *     tags={"Cart"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id","quantity"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=2, minimum=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Item added to cart",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or product out of stock"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = self::resolveSanctumUser($request);

        if (! $user) {
            throw new UnauthenticatedException();
        }

        return response()->json(
            $this->cartService->getUserCart($user)
        );
    }

    public function store(AddCartItemRequest $request): JsonResponse
    {
        $user = self::resolveSanctumUser($request);

        if (! $user) {
            throw new UnauthenticatedException();
        }

        $cartItem = $this->cartService->addItem(
            $user,
            $request->product_id,
            (int) $request->quantity
        );

        return response()->json($cartItem, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/cart/{cartItem}",
     *     summary="Update cart item quantity",
     *     tags={"Cart"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="cartItem",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quantity"},
     *             @OA\Property(property="quantity", type="integer", example=3, minimum=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cart item updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or product out of stock"
     *     )
     * )
     */
    public function update(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        $user = self::resolveSanctumUser($request);

        if (! $user) {
            throw new UnauthenticatedException();
        }

        $cartItem = $this->cartService->updateItem(
            $user,
            $cartItem,
            (int) $request->quantity
        );

        return response()->json($cartItem);
    }

    /**
     * @OA\Delete(
     *     path="/api/cart/{cartItem}",
     *     summary="Remove item from cart",
     *     tags={"Cart"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="cartItem",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item removed from cart",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Item removed from cart")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        $user = self::resolveSanctumUser($request);

        if (! $user) {
            throw new UnauthenticatedException();
        }

        $this->cartService->removeItem($user, $cartItem);

        return response()->json(['message' => 'Item removed from cart']);
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
