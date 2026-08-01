<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class PaymentFailedException extends Exception
{
    public function __construct(string $message = 'Payment processing failed. Please try again.')
    {
        parent::__construct($message, 500);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], $this->getCode() ?: 500);
    }
}
