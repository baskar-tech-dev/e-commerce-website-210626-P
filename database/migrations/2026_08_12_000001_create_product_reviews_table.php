<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create product_reviews table
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            
            $table->unsignedTinyInteger('rating');
            $table->text('review');
            $table->string('status', 20)->default('pending'); // pending, approved, rejected
            $table->boolean('is_verified_purchase')->default(false);
            $table->unsignedInteger('helpful_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes & constraints
            $table->unique(['product_id', 'user_id'], 'uniq_product_user_review');
            $table->index(['product_id', 'status']);
            $table->index('user_id');
            $table->index('status');
        });

        // 2. Create product_review_images table
        Schema::create('product_review_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_review_id')->constrained('product_reviews')->onDelete('cascade');
            $table->string('image_path', 255);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // 3. Create product_review_helpful_votes table
        Schema::create('product_review_helpful_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_review_id')->constrained('product_reviews')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['product_review_id', 'user_id'], 'uniq_review_user_helpful');
        });

        // 4. Add reviews_enabled to products table
        if (!Schema::hasColumn('products', 'reviews_enabled')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('reviews_enabled')->default(true)->after('is_returnable');
            });
        }

        // 5. Seed default settings for reviews in settings table
        $defaultSettings = [
            ['group' => 'reviews', 'key' => 'login_required', 'value' => '1', 'type' => 'boolean', 'description' => 'Require customers to log in before submitting a review.'],
            ['group' => 'reviews', 'key' => 'verified_purchase_required', 'value' => '1', 'type' => 'boolean', 'description' => 'Require customer to have purchased the product.'],
            ['group' => 'reviews', 'key' => 'delivered_order_required', 'value' => '1', 'type' => 'boolean', 'description' => 'Require customer order to be delivered/completed.'],
            ['group' => 'reviews', 'key' => 'one_review_per_product', 'value' => '1', 'type' => 'boolean', 'description' => 'Restrict each customer to one review per product.'],
            ['group' => 'reviews', 'key' => 'review_window_days', 'value' => '0', 'type' => 'number', 'description' => 'Allowed review window in days after delivery (0 = no restriction).'],
            ['group' => 'reviews', 'key' => 'admin_approval_required', 'value' => '1', 'type' => 'boolean', 'description' => 'Require admin approval before reviews appear publicly.'],
            ['group' => 'reviews', 'key' => 'customer_editing_allowed', 'value' => '1', 'type' => 'boolean', 'description' => 'Allow customers to edit their existing reviews.'],
            ['group' => 'reviews', 'key' => 'customer_deletion_allowed', 'value' => '1', 'type' => 'boolean', 'description' => 'Allow customers to delete their own reviews.'],
            ['group' => 'reviews', 'key' => 'customer_images_allowed', 'value' => '1', 'type' => 'boolean', 'description' => 'Allow customers to attach images to reviews.'],
            ['group' => 'reviews', 'key' => 'max_images_per_review', 'value' => '4', 'type' => 'number', 'description' => 'Maximum allowed photos per review.'],
            ['group' => 'reviews', 'key' => 'max_image_size_kb', 'value' => '200', 'type' => 'number', 'description' => 'Maximum final image size in KB after compression.'],
        ];

        foreach ($defaultSettings as $setting) {
            $exists = DB::table('settings')
                ->where('group', $setting['group'])
                ->where('key', $setting['key'])
                ->exists();

            if (!$exists) {
                DB::table('settings')->insert(array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // 6. Insert Product Reviews menu into menus table if menus table exists
        if (Schema::hasTable('menus')) {
            $menuExists = DB::table('menus')->where('path', '/admin/reviews')->exists();
            if (!$menuExists) {
                DB::table('menus')->insert([
                    'name' => 'Product Reviews',
                    'path' => '/admin/reviews',
                    'icon' => 'Star',
                    'group' => 'Catalog',
                    'permission_name' => 'manage_products',
                    'order' => 85,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_review_helpful_votes');
        Schema::dropIfExists('product_review_images');
        Schema::dropIfExists('product_reviews');

        if (Schema::hasColumn('products', 'reviews_enabled')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('reviews_enabled');
            });
        }

        DB::table('settings')->where('group', 'reviews')->delete();
    }
};
