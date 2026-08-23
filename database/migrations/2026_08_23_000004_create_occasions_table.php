<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('occasions')) {
            Schema::create('occasions', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('slug', 120)->unique();
                $table->string('image_url', 255)->nullable();
                $table->string('subtitle', 255)->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed default occasions with their asset images
            $defaultOccasions = [
                [
                    'name' => 'Bridal',
                    'slug' => 'bridal',
                    'image_url' => '/asset/occasion/wedding-Collection.png',
                    'subtitle' => 'Royal heavy work & bridal stretchable blouses',
                    'sort_order' => 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Wedding Collection',
                    'slug' => 'wedding-collection',
                    'image_url' => '/asset/occasion/wedding-Collection.png',
                    'subtitle' => 'Timeless wedding elegance & silk rich designs',
                    'sort_order' => 2,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Wedding Guest',
                    'slug' => 'wedding-guest',
                    'image_url' => '/asset/occasion/wedding-guest.png',
                    'subtitle' => 'Chic & graceful outfits for wedding attendees',
                    'sort_order' => 3,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Festival Collection',
                    'slug' => 'festival-collection',
                    'image_url' => '/asset/occasion/festival-collection.png',
                    'subtitle' => 'Vibrant festive wear & auspicious hues',
                    'sort_order' => 4,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Temple Wear',
                    'slug' => 'temple-wear',
                    'image_url' => '/asset/occasion/temple-collection.png',
                    'subtitle' => 'Sacred traditional motifs & temple styles',
                    'sort_order' => 5,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Family Functions',
                    'slug' => 'family-functions',
                    'image_url' => '/asset/occasion/family-function.png',
                    'subtitle' => 'Comfortable luxury for intimate get-togethers',
                    'sort_order' => 6,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Party Wear',
                    'slug' => 'party-wear',
                    'image_url' => '/asset/occasion/Party-wear.png',
                    'subtitle' => 'Glamorous evening styles & glitter statements',
                    'sort_order' => 7,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Office Wear',
                    'slug' => 'office-wear',
                    'image_url' => '/asset/occasion/office-wear.png',
                    'subtitle' => 'Sophisticated, elegant workwear essentials',
                    'sort_order' => 8,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Daily Wear',
                    'slug' => 'daily-wear',
                    'image_url' => '/asset/occasion/daily-wear.png',
                    'subtitle' => 'Everyday breathable comfort & graceful cuts',
                    'sort_order' => 9,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Traditional',
                    'slug' => 'traditional',
                    'image_url' => '/asset/occasion/temple-collection.png',
                    'subtitle' => 'Heritage classic weaves & patterns',
                    'sort_order' => 10,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            DB::table('occasions')->insert($defaultOccasions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occasions');
    }
};
