<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Color;
use App\Models\SizeGroup;
use App\Models\Size;

class ColorAndSizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Colors
        $colors = [
            ['name' => 'Crimson Maroon', 'code' => '#6B1124', 'sort_order' => 1],
            ['name' => 'Mustard Gold', 'code' => '#D4AF37', 'sort_order' => 2],
            ['name' => 'Bottle Green', 'code' => '#14532D', 'sort_order' => 3],
            ['name' => 'Jet Black', 'code' => '#18181B', 'sort_order' => 4],
            ['name' => 'Navy Blue', 'code' => '#1E3A8A', 'sort_order' => 5],
            ['name' => 'Rani Pink', 'code' => '#DB2777', 'sort_order' => 6],
            ['name' => 'Kasavu Cream', 'code' => '#FFF8E7', 'sort_order' => 7],
            ['name' => 'Royal Purple', 'code' => '#581C87', 'sort_order' => 8],
            ['name' => 'Peacock Teal', 'code' => '#0F766E', 'sort_order' => 9],
            ['name' => 'Ruby Red', 'code' => '#B91C1C', 'sort_order' => 10],
            ['name' => 'Olive Green', 'code' => '#4D7C0F', 'sort_order' => 11],
            ['name' => 'Rust Orange', 'code' => '#C2410C', 'sort_order' => 12],
            ['name' => 'Pastel Peach', 'code' => '#FDBA74', 'sort_order' => 13],
            ['name' => 'Silver Grey', 'code' => '#94A3B8', 'sort_order' => 14],
        ];

        foreach ($colors as $c) {
            Color::firstOrCreate(['name' => $c['name']], [
                'code' => $c['code'],
                'is_active' => true,
                'sort_order' => $c['sort_order'],
            ]);
        }

        // 2. Seed Size Groups & Sizes
        // Group 1: Stretchable Blouse Sizes
        $stretchGroup = SizeGroup::firstOrCreate(
            ['code' => 'STRETCH_BLOUSE'],
            [
                'name' => 'Stretchable Blouse Sizes',
                'description' => 'Cotton Lycra 4-Way Stretch Blouse Size Ranges',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $stretchSizes = [
            ['name' => '32-34', 'code' => '3234', 'measurement_hint' => 'Fits Bust 32" to 34"', 'sort_order' => 1],
            ['name' => '34-37', 'code' => '3437', 'measurement_hint' => 'Fits Bust 34" to 37"', 'sort_order' => 2],
            ['name' => '38-40', 'code' => '3840', 'measurement_hint' => 'Fits Bust 38" to 40"', 'sort_order' => 3],
            ['name' => '40-42', 'code' => '4042', 'measurement_hint' => 'Fits Bust 40" to 42"', 'sort_order' => 4],
            ['name' => 'Free Size', 'code' => 'FREE', 'measurement_hint' => 'Universal Flexible Stretch (32" - 40")', 'sort_order' => 5],
        ];

        foreach ($stretchSizes as $s) {
            Size::firstOrCreate(
                ['size_group_id' => $stretchGroup->id, 'name' => $s['name']],
                [
                    'code' => $s['code'],
                    'measurement_hint' => $s['measurement_hint'],
                    'is_active' => true,
                    'sort_order' => $s['sort_order'],
                ]
            );
        }

        // Group 2: Standard Saree Blouse Sizes
        $stdBlouseGroup = SizeGroup::firstOrCreate(
            ['code' => 'STD_BLOUSE'],
            [
                'name' => 'Standard Saree Blouse Sizes',
                'description' => 'Traditional Tailored Saree Blouse Sizes (Numeric)',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $stdSizes = [
            ['name' => '32', 'code' => '32', 'measurement_hint' => 'Bust 32 inches', 'sort_order' => 1],
            ['name' => '34', 'code' => '34', 'measurement_hint' => 'Bust 34 inches', 'sort_order' => 2],
            ['name' => '36', 'code' => '36', 'measurement_hint' => 'Bust 36 inches', 'sort_order' => 3],
            ['name' => '38', 'code' => '38', 'measurement_hint' => 'Bust 38 inches', 'sort_order' => 4],
            ['name' => '40', 'code' => '40', 'measurement_hint' => 'Bust 40 inches', 'sort_order' => 5],
            ['name' => '42', 'code' => '42', 'measurement_hint' => 'Bust 42 inches', 'sort_order' => 6],
            ['name' => '44', 'code' => '44', 'measurement_hint' => 'Bust 44 inches', 'sort_order' => 7],
        ];

        foreach ($stdSizes as $s) {
            Size::firstOrCreate(
                ['size_group_id' => $stdBlouseGroup->id, 'name' => $s['name']],
                [
                    'code' => $s['code'],
                    'measurement_hint' => $s['measurement_hint'],
                    'is_active' => true,
                    'sort_order' => $s['sort_order'],
                ]
            );
        }

        // Group 3: Alpha Garment Sizes
        $alphaGroup = SizeGroup::firstOrCreate(
            ['code' => 'ALPHA_SIZES'],
            [
                'name' => 'Standard Apparel Sizes (Alpha)',
                'description' => 'XS to 3XL Standard Apparel Sizes',
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        $alphaSizes = [
            ['name' => 'XS', 'code' => 'XS', 'measurement_hint' => 'Extra Small', 'sort_order' => 1],
            ['name' => 'S', 'code' => 'S', 'measurement_hint' => 'Small', 'sort_order' => 2],
            ['name' => 'M', 'code' => 'M', 'measurement_hint' => 'Medium', 'sort_order' => 3],
            ['name' => 'L', 'code' => 'L', 'measurement_hint' => 'Large', 'sort_order' => 4],
            ['name' => 'XL', 'code' => 'XL', 'measurement_hint' => 'Extra Large', 'sort_order' => 5],
            ['name' => '2XL', 'code' => '2XL', 'measurement_hint' => 'Double Extra Large', 'sort_order' => 6],
            ['name' => '3XL', 'code' => '3XL', 'measurement_hint' => 'Triple Extra Large', 'sort_order' => 7],
        ];

        foreach ($alphaSizes as $s) {
            Size::firstOrCreate(
                ['size_group_id' => $alphaGroup->id, 'name' => $s['name']],
                [
                    'code' => $s['code'],
                    'measurement_hint' => $s['measurement_hint'],
                    'is_active' => true,
                    'sort_order' => $s['sort_order'],
                ]
            );
        }
    }
}
