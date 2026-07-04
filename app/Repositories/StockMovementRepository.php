<?php

namespace App\Repositories;

use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StockMovementRepository
{

    public function create(array $data): StockMovement
    {
        return StockMovement::create($data);
    }


    public function getByProduct(int $productId, int $perPage = 15): LengthAwarePaginator
    {
        return StockMovement::query()->where('product_id', $productId)
            ->with(['creator:id,name,email', 'product:id,name,sku'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

   
    public function referenceExists(string $referenceNumber): bool
    {
        return StockMovement::query()->where('reference_number', $referenceNumber)->exists();
    }

    
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = StockMovement::with(['product:id,name,sku', 'creator:id,name']);

        if (isset($filters['movement_type'])) {
            $query->where('movement_type', $filters['movement_type']);
        }

        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
}