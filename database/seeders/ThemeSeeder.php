<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Theme;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Theme::create([
            'name' => 'Default Kasavu',
            'primary_color' => '#4a0e2e',
            'primary_color_hover' => '#6c1945',
            'secondary_color' => '#d4af37',
            'secondary_color_hover' => '#b59226',
            'is_active' => true,
        ]);
    }
}
