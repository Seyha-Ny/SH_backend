<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class CouponExpiredException extends Exception
{
    public function __construct(string $reason = 'Coupon is invalid or expired.')
    {
        parent::__construct($reason, 422);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => [
                'coupon_code' => [$this->getMessage()],
            ],
        ], $this->getCode() ?: 422);
    }
}
