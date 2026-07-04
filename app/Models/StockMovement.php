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
     
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStockImpact(): int
    {
        if ($this->movement_type === MovementType::ADJUSTMENT) {
            return $this->quantity;
        }

        return abs($this->quantity) * $this->movement_type->getStockImpact();
    }
}