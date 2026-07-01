<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderCancellationRequested;
use App\Mail\OrderReturnRequested;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
        $user = $request->user();

        $query = $user->orders()->with('items.product', 'shippingMethod')->orderByDesc('created_at');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $perPage = (int) ($request->query('per_page', 10));
        $orders = $query->paginate($perPage);

        return response()->json($orders);
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
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $order->load('items.product', 'shippingMethod');
        return response()->json($order);
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
        $request->validate([
            'shipping_method_id' => ['nullable', 'integer', 'exists:courier_shipping_methods,id'],
        ]);

        $cartItems = $request->user()->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
        }

        foreach ($cartItems as $item) {
            if ($item->product->stock < $item->quantity) {
                return response()->json([
                    'message' => 'Insufficient stock for ' . $item->product->name,
                ], 422);
            }
        }

        $selectedShippingMethod = \App\Models\ShippingMethod::find($request->input('shipping_method_id'));
        $shippingFee = $selectedShippingMethod ? (float) $selectedShippingMethod->fee : 0;

        $order = DB::transaction(function () use ($request, $cartItems, $shippingFee, $selectedShippingMethod) {
            $order = Order::create([
                'user_id' => $request->user()->id,
                'status' => 'pending',
                'total' => $cartItems->sum(function ($item) {
                    return $item->product->price * $item->quantity;
                }) + $shippingFee,
                'shipping_amount' => $shippingFee,
                'shipping_method_id' => $selectedShippingMethod?->id,
            ]);

            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);

                $item->product->decrement('stock', $item->quantity);
            }

            CartItem::where('user_id', $request->user()->id)->delete();

            return $order;
        });

        $order->load('items.product', 'shippingMethod');

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
        if ($order->user_id !== $request->user()->id) {
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
    public function requestCancel(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        if (! in_array($order->status, ['pending', 'processing'], true)) {
            return response()->json(['message' => 'Order cannot be canceled at this stage.'], 422);
        }

        $order->update([
            'status' => 'cancellation_requested',
        ]);

        if ($order->user && filter_var($order->user->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($order->user->email)->send(new OrderCancellationRequested($order, $validated['reason'] ?? null));
            } catch (\Throwable $e) {
                // ignore mail failures
            }
        }

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
    public function requestReturn(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        if (! in_array($order->status, ['completed', 'processing'], true)) {
            return response()->json(['message' => 'Order is not eligible for return.'], 422);
        }

        $order->update([
            'status' => 'return_requested',
        ]);

        if ($order->user && filter_var($order->user->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($order->user->email)->send(new OrderReturnRequested($order, $validated['reason'] ?? null));
            } catch (\Throwable $e) {
                // ignore mail failures
            }
        }

        return response()->json(['message' => 'Return request submitted.']);
    }
}
