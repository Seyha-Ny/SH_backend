<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;
class CouponController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/coupons/validate",
     *     summary="Validate coupon code",
     *     tags={"Coupons"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code"},
     *             @OA\Property(property="code", type="string", example="SUMMER2024")
     *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Coupon is valid",
 *         @OA\JsonContent(
 *             @OA\Property(property="valid", type="boolean", example=true),
 *             @OA\Property(property="type", type="string", example="percentage"),
 *             @OA\Property(property="value", type="number", format="float", example=20),
 *             @OA\Property(property="message", type="string", example="Coupon gives 20% off")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Coupon is invalid or expired"
 *     )
 * )
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $code = strtoupper(trim((string) $request->input('code')));
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon || ! $coupon->isActiveNow()) {
            return response()->json(['valid' => false, 'message' => 'Coupon is invalid or expired.'], 422);
        }

        if ($coupon->max_uses !== null && $coupon->used_count >= $coupon->max_uses) {
            return response()->json(['valid' => false, 'message' => 'Coupon usage limit reached.'], 422);
        }

        return response()->json([
            'valid' => true,
            'type' => $coupon->type,
            'value' => (float) $coupon->value,
            'message' => $coupon->type === 'percentage'
                ? "Coupon gives {$coupon->value}% off"
                : "Coupon gives $" . number_format($coupon->value, 2) . ' off',
        ]);
    }
}
