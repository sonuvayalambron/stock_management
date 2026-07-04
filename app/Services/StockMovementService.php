<?php

namespace App\Services;

use App\Enums\MovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\StockMovement;
use App\Repositories\ProductRepository;
use App\Repositories\StockMovementRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class StockMovementService
{
    private ProductRepository $productRepository;
    private StockMovementRepository $movementRepository;

    public function __construct(
        ProductRepository $productRepository,
        StockMovementRepository $movementRepository
    ) {
        $this->productRepository = $productRepository;
        $this->movementRepository = $movementRepository;
    }

    //stock movement
    public function createMovement(array $data): StockMovement
    {
        return DB::transaction(function () use ($data) {

            $product = $this->productRepository->findOrFail($data['product_id']);
            $stockBalance = $this->productRepository->getStockBalanceWithLock($product->id);
            
            $movementType = MovementType::from($data['movement_type']);
            
            $quantity = $movementType === MovementType::ADJUSTMENT 
                ? (int) $data['quantity']
                : abs((int) $data['quantity']);
            $stockImpact = $this->calculateStockImpact($movementType, $quantity);
            
            if ($stockImpact < 0) {
                $this->validateStockAvailability(
                    $product,
                    $stockBalance->quantity,
                    abs($stockImpact)
                );
            }
            
            $movement = $this->movementRepository->create([
                'product_id' => $product->id,
                'movement_type' => $movementType->value,
                'quantity' => $quantity, // ✅ Now stores correct sign for adjustments
                'reference_number' => $data['reference_number'],
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);
            
            $this->productRepository->updateStockBalance($product->id, $stockImpact);
            
            Log::info('Stock movement created', [
                'movement_id' => $movement->id,
                'product' => $product->name,
                'type' => $movementType->value,
                'quantity' => $quantity,
                'impact' => $stockImpact,
                'new_balance' => $stockBalance->quantity + $stockImpact,
            ]);
            
            return $movement->load(['product', 'creator']);
        });
    }


    private function calculateStockImpact(MovementType $type, int $quantity): int
    {
  
        if ($type === MovementType::ADJUSTMENT) {
            return $quantity; // -5 returns -5, +10 returns +10
        }
        return abs($quantity) * $type->getStockImpact();
    }

    private function validateStockAvailability(Product $product, int $currentStock, int $required): void
    {
        if ($currentStock < $required) {
            throw new InsufficientStockException(
                $product->name,
                $currentStock,
                $required
            );
        }
    }

    public function getProductMovements(int $productId, int $perPage = 15)
    {
        $this->productRepository->findOrFail($productId);
        return $this->movementRepository->getByProduct($productId, $perPage);
    }

    public function getProductStock(int $productId): array
    {
        $product = $this->productRepository->findOrFail($productId);
        $stockBalance = $product->stockBalance;

        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'is_active' => $product->is_active,
            'current_stock' => $stockBalance?->quantity ?? 0,
            'last_movement_at' => $stockBalance?->last_movement_at?->toIso8601String(),
            'updated_at' => $stockBalance?->updated_at?->toIso8601String(),
        ];
    }

    public function getAllMovements(array $filters = [], int $perPage = 15)
    {
        return $this->movementRepository->getAll($filters, $perPage);
    }
}