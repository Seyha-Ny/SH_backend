<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class CartEmptyException extends Exception
{
    public function __construct()
    {
        parent::__construct('Your cart is empty. Please add items before checking out.', 400);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], $this->getCode() ?: 400);
    }
}
