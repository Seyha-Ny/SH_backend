<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class OrderNotEligibleForReturnException extends Exception
{
    public function __construct(string $currentStatus = '')
    {
        $message = $currentStatus
            ? "Order is not eligible for return in its current status: '{$currentStatus}'."
            : 'Order is not eligible for return.';
        parent::__construct($message, 422);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], $this->getCode() ?: 422);
    }
}
