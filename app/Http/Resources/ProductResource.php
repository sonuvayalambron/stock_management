<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'is_active' => (bool) $this->is_active,
            'current_stock' => $this->when(
                $this->relationLoaded('stockBalance'),
                $this->stockBalance?->quantity ?? 0
            ),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
