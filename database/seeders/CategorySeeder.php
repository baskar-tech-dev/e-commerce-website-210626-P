<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'ReadyMade Blouse',
                'slug' => 'readymade-blouse',
                'description' => 'Ready-made stretchable & designer blouses',
                'image' => '/asset/Bottle-Green-Designer-Stretchable-Blouse.jpeg',
                'sort_order' => 1,
                'is_active' => true,
                'is_featured' => true
            ],
            [
                'name' => 'Womens',
                'slug' => 'womens',
                'description' => 'South Indian Women Fashion Wear & Sarees',
                'image' => '/storage/products/1/webp/71a5c1c3-186d-4a58-8135-1b523de86e6a.webp',
                'sort_order' => 2,
                'is_active' => true,
                'is_featured' => true
            ],
            [
                'name' => 'Kids',
                'slug' => 'kids',
                'description' => 'Kids & Girls ethnic wear collections',
                'image' => '/asset/Baby-Girls-Floral-Print-Cotton.jpeg',
                'sort_order' => 3,
                'is_active' => true,
                'is_featured' => true
            ],
            [
                'name' => 'Mens',
                'slug' => 'mens',
                'description' => 'Traditional ethnic wear for mens',
                'image' => '/asset/Baby-Girl-Sleeveless-Striped-Dress.jpeg',
                'sort_order' => 4,
                'is_active' => true,
                'is_featured' => true
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat
            );
        }

        // Unfeature all other categories so only these 4 display on the homepage
        Category::whereNotIn('slug', ['readymade-blouse', 'womens', 'kids', 'mens'])
            ->update(['is_featured' => false]);
    }
}
