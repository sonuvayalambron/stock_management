<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quantity',
        'last_movement_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'last_movement_at' => 'datetime',
    ];

    /**
     * Get the product this balance belongs to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}