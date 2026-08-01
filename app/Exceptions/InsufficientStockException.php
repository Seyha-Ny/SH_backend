<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsufficientStockException extends Exception
{
    public function __construct(
        string $productName = '',
        int $available = 0,
        int $requested = 0
    ) {
        $message = $productName
            ? "Insufficient stock for '{$productName}'. Available: {$available}, requested: {$requested}."
            : 'Insufficient stock for one or more items in your cart.';
        parent::__construct($message, 422);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => [
                'stock' => [$this->getMessage()],
            ],
        ], $this->getCode() ?: 422);
    }
}
