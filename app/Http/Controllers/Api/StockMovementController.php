<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStockMovementRequest;
use App\Services\StockMovementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\StockMovementResource;
use App\Http\Resources\ProductResource;

class StockMovementController extends Controller
{
    public function __construct(
        private StockMovementService $stockMovementService
    ) {}

    //create new stock movement
    public function store(StoreStockMovementRequest $request): JsonResponse
    {
        $movement = $this->stockMovementService->createMovement(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Stock movement created successfully',
            'data' => new StockMovementResource($movement),
        ], 201);
    }

    //get all stock movements
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['movement_type', 'product_id']);
        $perPage = $request->input('per_page', 15);

        $movements = $this->stockMovementService->getAllMovements($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => StockMovementResource::collection($movements)->response()->getData(true),
        ]);
    }
}