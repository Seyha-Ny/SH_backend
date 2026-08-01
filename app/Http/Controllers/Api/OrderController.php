<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderActionRequest;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/orders",
 *     summary="Get user's orders",
 *     tags={"Orders"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="integer", default=10)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful response",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="array", items={@OA\Items(type="object")}),
 *             @OA\Property(property="current_page", type="integer"),
 *             @OA\Property(property="last_page", type="integer"),
 *             @OA\Property(property="per_page", type="integer"),
 *             @OA\Property(property="total", type="integer")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated"
 *     )
 * )
 */
class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/orders/{order}",
     *     summary="Get order details",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="status", type="string", example="pending"),
     *             @OA\Property(property="total", type="number", format="float", example=199.99),
     *             @OA\Property(property="shipping_amount", type="number", format="float", example=10.00),
     *             @OA\Property(property="items", type="array", items={@OA\Items(type="object")}),
     *             @OA\Property(property="shipping_method", type="object")
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
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json(
            $this->orderService->getUserOrders(
                $user,
                $request->query('status'),
                (int) $request->query('per_page', 10)
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/api/checkout",
     *     summary="Checkout and create order",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="shipping_method_id", type="integer", example=1, nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="status", type="string", example="pending"),
     *             @OA\Property(property="total", type="number", format="float", example=199.99),
     *             @OA\Property(property="shipping_amount", type="number", format="float", example=10.00),
     *             @OA\Property(property="items", type="array", items={@OA\Items(type="object")}),
     *             @OA\Property(property="shipping_method", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Cart is empty"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Insufficient stock"
     *     )
     * )
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        $user = $request->user('sanctum');

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json(
            $this->orderService->getOrder($user, $order)
        );
    }

    /**
     * @OA\Get(
     *     path="/api/orders/{order}/invoice",
     *     summary="Get order invoice",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoice HTML",
     *         @OA\MediaType(
     *             mediaType="text/html"
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
    public function checkout(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $order = $this->orderService->checkout(
            $user,
            $request->input('shipping_method_id'),
            $request->input('coupon_code')
        );

        return response()->json($order, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/orders/{order}/cancel",
     *     summary="Request order cancellation",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="reason", type="string", example="Changed my mind", nullable=true, maxLength=500)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cancellation request submitted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cancellation request submitted.")
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
     *         description="Order cannot be canceled at this stage"
     *     )
     * )
     */
    public function invoice(Request $request, Order $order): \Illuminate\Http\Response
    {
        $user = $request->user('sanctum');

        if (! $user || $order->user_id !== $user->id) {
            abort(403);
        }

        $order->load('items.product', 'shippingMethod');

        $html = view('invoices.show', [
            'order' => $order,
        ])->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/orders/{order}/return",
     *     summary="Request order return",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="reason", type="string", example="Product damaged", nullable=true, maxLength=500)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Return request submitted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Return request submitted.")
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
     *         description="Order is not eligible for return"
     *     )
     * )
     */
    public function requestCancel(OrderActionRequest $request, Order $order): JsonResponse
    {
        $user = $request->user('sanctum');

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $this->orderService->requestCancel(
            $user,
            $order,
            $request->input('reason')
        );

        return response()->json(['message' => 'Cancellation request submitted.']);
    }

    /**
     * @OA\Put(
     *     path="/api/orders/{id}/cancel",
     *     summary="Request order cancellation by ID",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="reason", type="string", example="Changed my mind", nullable=true, maxLength=500)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cancellation request submitted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cancellation request submitted.")
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
     *         description="Order cannot be canceled at this stage"
     *     )
     * )
     */
    public function requestReturn(OrderActionRequest $request, Order $order): JsonResponse
    {
        $user = $request->user('sanctum');

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $this->orderService->requestReturn(
            $user,
            $order,
            $request->input('reason')
        );

        return response()->json(['message' => 'Return request submitted.']);
    }
}
