<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('section_badges')) {
            Schema::create('section_badges', function (Blueprint $table) {
                $table->id();
                $table->string('title', 100);
                $table->string('filter_type', 50)->default('badge'); // badge, new_arrival, bestseller, featured, occasion
                $table->string('badge_key', 100)->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed default 10 badges
            $defaults = [
                ['title' => 'New Arrivals', 'filter_type' => 'new_arrival', 'badge_key' => null, 'sort_order' => 1, 'is_active' => true],
                ['title' => 'Best Sellers', 'filter_type' => 'bestseller', 'badge_key' => null, 'sort_order' => 2, 'is_active' => true],
                ['title' => 'Trending', 'filter_type' => 'featured', 'badge_key' => null, 'sort_order' => 3, 'is_active' => true],
                ['title' => 'Premium Collection', 'filter_type' => 'badge', 'badge_key' => 'Premium Collection', 'sort_order' => 4, 'is_active' => true],
                ['title' => 'Designer', 'filter_type' => 'badge', 'badge_key' => 'Designer', 'sort_order' => 5, 'is_active' => true],
                ['title' => 'Embroidered', 'filter_type' => 'badge', 'badge_key' => 'Embroidered', 'sort_order' => 6, 'is_active' => true],
                ['title' => 'Mirror Work', 'filter_type' => 'badge', 'badge_key' => 'Mirror Work', 'sort_order' => 7, 'is_active' => true],
                ['title' => 'Stone Work', 'filter_type' => 'badge', 'badge_key' => 'Stone Work', 'sort_order' => 8, 'is_active' => true],
                ['title' => 'Floral Collection', 'filter_type' => 'badge', 'badge_key' => 'Floral Collection', 'sort_order' => 9, 'is_active' => true],
                ['title' => 'Temple Collection', 'filter_type' => 'badge', 'badge_key' => 'Temple Collection', 'sort_order' => 10, 'is_active' => true],
            ];

            $now = now();
            foreach ($defaults as $d) {
                DB::table('section_badges')->insert(array_merge($d, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_badges');
    }
};
