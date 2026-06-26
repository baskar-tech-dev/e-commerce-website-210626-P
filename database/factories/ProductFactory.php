<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->sentence(3);
        $mrp = $this->faker->randomFloat(2, 500, 5000);
        $sellingPrice = $this->faker->randomFloat(2, 100, $mrp);

        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'short_description' => $this->faker->paragraph(1),
            'description' => $this->faker->paragraph(3),
            'material' => $this->faker->word(),
            'care_instructions' => $this->faker->sentence(),
            'mrp' => $mrp,
            'selling_price' => $sellingPrice,
            'cost_price' => $sellingPrice * 0.6,
            'tax_category' => 'standard',
            'gst_rate' => 18.00,
            'hsn_code' => $this->faker->numerify('HSN####'),
            'weight' => $this->faker->randomFloat(2, 100, 1000),
            'is_active' => true,
            'is_featured' => false,
            'is_new_arrival' => false,
            'is_bestseller' => false,
            'is_returnable' => true,
            'return_window_days' => 7,
            'avg_rating' => 0.00,
            'total_reviews' => 0,
            'total_sold' => 0,
            'meta_title' => $name,
            'meta_description' => $this->faker->sentence(),
            'meta_keywords' => implode(',', $this->faker->words(5)),
        ];
    }
}
