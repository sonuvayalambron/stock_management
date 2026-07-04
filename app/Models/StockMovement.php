<?php

namespace App\Models;

use App\Enums\MovementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'movement_type',
        'quantity',
        'reference_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'movement_type' => MovementType::class,
        'quantity' => 'integer',
    ];

    /**
     * Get the product that this movement belongs to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who created this movement
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Calculate the actual stock impact of this movement
     */
    public function getStockImpact(): int
    {
        // For adjustment, quantity can be positive or negative
        if ($this->movement_type === MovementType::ADJUSTMENT) {
            return $this->quantity;
        }

        // For other types, apply the multiplier
        return abs($this->quantity) * $this->movement_type->getStockImpact();
    }
}