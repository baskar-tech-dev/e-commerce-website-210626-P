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
            'collection_name' => $this->collection_name,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'subtitle' => $this->subtitle,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'occasion' => $this->occasion,
            'season' => $this->season,
            'style' => $this->style,
            'material' => $this->material,
            'fabric' => $this->fabric,
            'pattern' => $this->pattern,
            'sleeve' => $this->sleeve,
            'neck' => $this->neck,
            'fit' => $this->fit,
            'care_instructions' => $this->care_instructions,
            'alt_text' => $this->alt_text,
            'mrp' => $this->mrp,
            'selling_price' => $this->selling_price,
            'discount_percentage' => $this->discount_percentage,
            'cost_price' => $this->cost_price,
            'tax_category' => $this->tax_category,
            'gst_rate' => $this->gst_rate,
            'hsn_code' => $this->hsn_code,
            'weight' => $this->weight,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'is_new_arrival' => $this->is_new_arrival,
            'is_bestseller' => $this->is_bestseller,
            'is_premium' => $this->is_premium,
            'is_trending' => $this->is_trending,
            'is_editors_pick' => $this->is_editors_pick,
            'sort_order' => $this->sort_order,
            'is_returnable' => $this->is_returnable,
            'return_window_days' => $this->return_window_days,
            'avg_rating' => $this->avg_rating,
            'total_reviews' => $this->total_reviews,
            'total_sold' => $this->total_sold,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
