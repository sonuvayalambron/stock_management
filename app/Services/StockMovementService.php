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

class StockMovementService
{
    public function __construct(
        private ProductRepository $productRepository,
        private StockMovementRepository $movementRepository
    ) {}

    /**
     * Create a stock movement with full transaction safety
     * 
     * @throws InsufficientStockException
     * @throws \Exception
     */
    public function createMovement(array $data): StockMovement
    {
        return DB::transaction(function () use ($data) {
            // 1. Validate product exists
            $product = $this->productRepository->findOrFail($data['product_id']);
            
            // 2. Get current stock balance with row lock (prevents concurrent issues)
            $stockBalance = $this->productRepository->getStockBalanceWithLock($product->id);
            
            // 3. Parse movement type
            $movementType = MovementType::from($data['movement_type']);
            $quantity = abs((int) $data['quantity']);
            
            // 4. Calculate stock impact
            $stockImpact = $this->calculateStockImpact($movementType, $quantity, $data);
            
            // 5. Validate stock availability (before creating movement)
            if ($stockImpact < 0) {
                $this->validateStockAvailability(
                    $product,
                    $stockBalance->quantity,
                    abs($stockImpact)
                );
            }
            
            // 6. Create the movement record
            $movement = $this->movementRepository->create([
                'product_id' => $product->id,
                'movement_type' => $movementType->value,
                'quantity' => $quantity,
                'reference_number' => $data['reference_number'],
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);
            
            // 7. Update stock balance
            $this->productRepository->updateStockBalance($product->id, $stockImpact);
            
            // 8. Log the movement
            Log::info('Stock movement created', [
                'movement_id' => $movement->id,
                'product' => $product->name,
                'type' => $movementType->value,
                'impact' => $stockImpact,
                'new_balance' => $stockBalance->quantity + $stockImpact,
            ]);
            
            // 9. Return with relationships loaded
            return $movement->load(['product', 'creator']);
        });
    }

    /**
     * Calculate the actual stock impact
     */
    private function calculateStockImpact(MovementType $type, int $quantity, array $data): int
    {
        // For adjustments, quantity can be positive (increase) or negative (decrease)
        if ($type === MovementType::ADJUSTMENT) {
            return (int) $data['quantity']; // Keep original sign
        }

        // For other types, apply the movement type multiplier
        return $quantity * $type->getStockImpact();
    }

    /**
     * Validate if enough stock is available for decrease operations
     * 
     * @throws InsufficientStockException
     */
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

    /**
     * Get stock movements for a product
     */
    public function getProductMovements(int $productId, int $perPage = 15)
    {
        // Validate product exists
        $this->productRepository->findOrFail($productId);
        
        return $this->movementRepository->getByProduct($productId, $perPage);
    }

    /**
     * Get current stock information for a product
     */
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

    /**
     * Get all movements with optional filters
     */
    public function getAllMovements(array $filters = [], int $perPage = 15)
    {
        return $this->movementRepository->getAll($filters, $perPage);
    }
}