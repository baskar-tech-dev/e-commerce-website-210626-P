<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Ensure Women parent category exists
        $women = Category::firstOrCreate(
            ['slug' => 'women'],
            [
                'name' => 'Women',
                'description' => 'South Indian Women Fashion Wear',
                'image' => '/storage/products/1/webp/71a5c1c3-186d-4a58-8135-1b523de86e6a.webp',
                'sort_order' => 1,
                'is_active' => true,
                'is_featured' => true
            ]
        );

        // Subcategories under Women
        $subcategories = [
            [
                'name' => 'Sarees',
                'slug' => 'sarees',
                'description' => 'Traditional Silk, Kora Silk, & Soft Cotton Sarees',
                'image' => '/storage/products/1/webp/71a5c1c3-186d-4a58-8135-1b523de86e6a.webp',
                'sort_order' => 1
            ],
            [
                'name' => 'Blouses',
                'slug' => 'blouses',
                'description' => 'Ready-made stretchable & designer blouses',
                'image' => '/asset/Bottle-Green-Designer-Stretchable-Blouse.jpeg',
                'sort_order' => 2
            ],
            [
                'name' => 'Kurtis',
                'slug' => 'kurtis',
                'description' => 'Comfort-fit daily wear & ethnic kurtis',
                'image' => '/storage/products/3/webp/35ec359a-7767-493f-9c88-ec3ed914275e.webp',
                'sort_order' => 3
            ],
            [
                'name' => 'Dupatta',
                'slug' => 'dupatta',
                'description' => 'Silk, Organza & Zari embroidered dupattas',
                'image' => '/storage/products/4/webp/029bbc54-5a9c-4467-b7d7-af9778fad24f.webp',
                'sort_order' => 4
            ]
        ];

        foreach ($subcategories as $sub) {
            Category::firstOrCreate(
                ['slug' => $sub['slug']],
                [
                    'parent_id' => $women->id,
                    'name' => $sub['name'],
                    'description' => $sub['description'],
                    'image' => $sub['image'],
                    'sort_order' => $sub['sort_order'],
                    'is_active' => true,
                    'is_featured' => true
                ]
            );
        }

        // Additional root categories
        Category::firstOrCreate(
            ['slug' => 'girls'],
            [
                'name' => 'Girls',
                'description' => 'Kids & Girls ethnic wear',
                'image' => '/asset/Baby-Girls-Floral-Print-Cotton.jpeg',
                'sort_order' => 2,
                'is_active' => true,
                'is_featured' => false
            ]
        );

        Category::firstOrCreate(
            ['slug' => 'boys'],
            [
                'name' => 'Boys',
                'description' => 'Traditional ethnic wear for boys',
                'image' => '/asset/Baby-Girl-Sleeveless-Striped-Dress.jpeg',
                'sort_order' => 3,
                'is_active' => true,
                'is_featured' => false
            ]
        );
    }
}
