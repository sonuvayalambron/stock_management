<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\StockBalance;
use Illuminate\Support\Facades\DB;

class ProductRepository
{

    public function findOrFail(int $id): Product
    {
        return Product::with('stockBalance')->findOrFail($id);
    }

    public function getStockBalanceWithLock(int $productId): StockBalance
    {
        return StockBalance::lockForUpdate()
            ->firstOrCreate(
                ['product_id' => $productId],
                ['quantity' => 0, 'last_movement_at' => null]
            );
    }

   
    public function updateStockBalance(int $productId, int $quantityChange): void
    {
        StockBalance::query()->where('product_id', $productId)
            ->update([
                'quantity' => DB::raw("quantity + ({$quantityChange})"),
                'last_movement_at' => now(),
                'updated_at' => now(),
            ]);
    }

    
    public function getAllActive()
    {
        return Product::query()->where('is_active', true)
            ->with('stockBalance')
            ->get();
    }
}