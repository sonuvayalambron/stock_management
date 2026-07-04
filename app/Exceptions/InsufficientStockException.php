<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class InsufficientStockException extends Exception
{
    public function __construct(
        public string $productName,
        public int $available,
        public int $required
    ) {
        $message = "Insufficient stock for '{$productName}'. Available: {$available}, Required: {$required}";
        parent::__construct($message, 422);
    }

    /**
     * Render the exception as an HTTP response
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'error' => 'insufficient_stock',
            'data' => [
                'product' => $this->productName,
                'available' => $this->available,
                'required' => $this->required,
            ]
        ], $this->getCode());
    }
}