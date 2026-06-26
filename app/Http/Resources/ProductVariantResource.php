<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'size' => $this->size,
            'color' => $this->color,
            'color_code' => $this->color_code,
            'mrp' => $this->mrp,
            'selling_price' => $this->selling_price,
            'cost_price' => $this->cost_price,
            'stock_quantity' => $this->stock_quantity,
            'reserved_quantity' => $this->reserved_quantity,
            'low_stock_threshold' => $this->low_stock_threshold,
            'weight' => $this->weight,
            'barcode' => $this->barcode,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
