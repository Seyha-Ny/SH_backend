<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStripeSessionRequest;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

class PaymentController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected PaymentService $paymentService
    ) {}
    /**
     * @OA\Post(
 * path="/api/payment/stripe-webhook",
 * summary="Stripe webhook handler",
 * tags={"Payments"},
 * @OA\RequestBody(
 *     required=true,
 *     description="Stripe webhook payload",
 *     @OA\JsonContent()
 * ),
 * @OA\Response(
 *     response=200,
 *     description="Webhook handled",
 *     @OA\JsonContent(
 *         @OA\Property(property="message", type="string", example="Webhook handled")
 *     )
 * ),
 * @OA\Response(
 *     response=400,
 *     description="Invalid signature"
 * )
 * )
     */
    /**
     * @OA\Post(
     *     path="/api/payment/stripe-session",
     *     summary="Create Stripe checkout session",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"address","city","postal_code","phone"},
     *             @OA\Property(property="address", type="string", example="123 Main St"),
     *             @OA\Property(property="city", type="string", example="New York"),
     *             @OA\Property(property="postal_code", type="string", example="10001"),
     *             @OA\Property(property="phone", type="string", example="+123****7890"),
     *             @OA\Property(property="shipping_method_id", type="integer", nullable=true, example=1),
     *             @OA\Property(property="coupon_code", type="string", nullable=true, example="SUMMER2024")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stripe session created",
     *         @OA\JsonContent(
     *             @OA\Property(property="url", type="string", example="https://checkout.stripe.com/...")
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
     *         description="Validation error or insufficient stock"
     *     )
     * )
     */
    public function stripeSession(CreateStripeSessionRequest $request): JsonResponse
    {
        $user = $request->user('sanctum');

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        try {
            // Create order via OrderService
            $order = $this->orderService->checkout(
                $user,
                $request->input('shipping_method_id'),
                $request->input('coupon_code')
            );

            // Add payment-specific fields
            $order->update([
                'payment_status' => 'pending_payment',
                'payment_method' => 'stripe',
                'shipping_address' => $request->input('address'),
                'shipping_city' => $request->input('city'),
                'shipping_postal_code' => $request->input('postal_code'),
                'shipping_phone' => $request->input('phone'),
            ]);

            // Create Stripe session
            $session = $this->paymentService->createStripeSession($order);

            if (! $session) {
                // Stripe not configured, return order directly
                return response()->json([
                    'message' => 'Checkout placed successfully.',
                    'order_id' => $order->id,
                    'order' => $order->fresh()->load('items.product', 'shippingMethod'),
                    'payment_method' => 'direct',
                ]);
            }

            return response()->json(['url' => $session->url]);
        } catch (\Throwable $e) {
            Log::error('Stripe session creation failed.', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * @OA\Post(
 * path="/api/payment/stripe-webhook",
 * summary="Stripe webhook handler",
 * tags={"Payments"},
 * @OA\RequestBody(
 *     required=true,
 *     description="Stripe webhook payload",
 *     @OA\JsonContent()
 * ),
 * @OA\Response(
 *     response=200,
 *     description="Webhook handled",
 *     @OA\JsonContent(
 *         @OA\Property(property="message", type="string", example="Webhook handled")
 *     )
 * ),
 * @OA\Response(
 *     response=400,
 *     description="Invalid signature"
 * )
 * )
     */
    public function stripeWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $this->paymentService->handleWebhook($payload, $sigHeader);

            return response()->json(['message' => 'Webhook handled']);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed.', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Invalid signature'], 400);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook processing failed.', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Webhook processing failed'], 500);
        }
    }
}
