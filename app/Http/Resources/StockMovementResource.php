<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'movement_type' => $this->movement_type->value,
            'quantity' => $this->quantity,
            'reference_number' => $this->reference_number,
            'notes' => $this->notes,
            'product' => new ProductResource($this->whenLoaded('product')),
            'created_by' => $this->creator?->name,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
