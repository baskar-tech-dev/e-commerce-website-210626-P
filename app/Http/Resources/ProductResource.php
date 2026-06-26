<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'uuid' => $this->uuid,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'material' => $this->material,
            'care_instructions' => $this->care_instructions,
            'mrp' => $this->mrp,
            'selling_price' => $this->selling_price,
            'cost_price' => $this->cost_price,
            'tax_category' => $this->tax_category,
            'gst_rate' => $this->gst_rate,
            'hsn_code' => $this->hsn_code,
            'weight' => $this->weight,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'is_new_arrival' => $this->is_new_arrival,
            'is_bestseller' => $this->is_bestseller,
            'is_returnable' => $this->is_returnable,
            'return_window_days' => $this->return_window_days,
            'avg_rating' => $this->avg_rating,
            'total_reviews' => $this->total_reviews,
            'total_sold' => $this->total_sold,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
