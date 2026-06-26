<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => 'SKU-' . $this->faker->unique()->regexify('[A-Z0-9]{8}'),
            'size' => $this->faker->randomElement(['S', 'M', 'L', 'XL']),
            'color' => $this->faker->safeColorName(),
            'color_code' => $this->faker->hexColor(),
            'mrp' => null,
            'selling_price' => null,
            'cost_price' => null,
            'stock_quantity' => $this->faker->numberBetween(10, 100),
            'reserved_quantity' => 0,
            'low_stock_threshold' => 5,
            'weight' => null,
            'barcode' => $this->faker->isbn10(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
