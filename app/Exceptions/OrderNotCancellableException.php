<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class OrderNotCancellableException extends Exception
{
    public function __construct(string $currentStatus = '')
    {
        $message = $currentStatus
            ? "Order cannot be canceled in its current status: '{$currentStatus}'."
            : 'Order cannot be canceled at this stage.';
        parent::__construct($message, 422);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], $this->getCode() ?: 422);
    }
}
