<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class UnauthenticatedException extends Exception
{
    public function __construct(string $message = 'Unauthenticated. Please log in.')
    {
        parent::__construct($message, 401);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
        ], $this->getCode() ?: 401);
    }
}
