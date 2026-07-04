<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StockMovementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\StockMovementResource;
use App\Http\Resources\ProductResource;

class ProductStockController extends Controller
{
    public function __construct(
        private StockMovementService $stockMovementService
    ) {}

    //current stock
    public function show(int $productId): JsonResponse
    {
        $stock = $this->stockMovementService->getProductStock($productId);

        return response()->json([
            'success' => true,
            'data' => $stock,
        ]);
    }

    //all movements of product
    public function movements(int $productId, Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);

        $movements = $this->stockMovementService->getProductMovements($productId, $perPage);

        return response()->json([
            'success' => true,
            'data' => StockMovementResource::collection($movements)->response()->getData(true),
        ]);
    }
}