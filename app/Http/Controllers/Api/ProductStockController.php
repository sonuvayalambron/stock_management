<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StockMovementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductStockController extends Controller
{
    public function __construct(
        private StockMovementService $stockMovementService
    ) {}

    /**
     * Get current stock for a product
     */
    public function show(int $productId): JsonResponse
    {
        $stock = $this->stockMovementService->getProductStock($productId);

        return response()->json([
            'success' => true,
            'data' => $stock,
        ]);
    }

    /**
     * Get all stock movements for a product
     */
    public function movements(int $productId, Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $movements = $this->stockMovementService->getProductMovements($productId, $perPage);

        return response()->json([
            'success' => true,
            'data' => $movements,
        ]);
    }
}