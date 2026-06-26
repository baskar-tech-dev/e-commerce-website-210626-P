<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductImage>
 */
class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'variant_id' => null,
            'url' => $this->faker->imageUrl(),
            'thumbnail_url' => $this->faker->imageUrl(150, 150),
            'alt_text' => $this->faker->sentence(),
            'sort_order' => 0,
            'is_primary' => false,
        ];
    }
}
