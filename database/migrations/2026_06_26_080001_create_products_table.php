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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('category_id')
                ->constrained('categories')
                ->onDelete('restrict');

            $table->string('name', 300);
            $table->string('slug', 350)->unique();
            $table->string('short_description', 500)->nullable();
            $table->text('description')->nullable();
            $table->string('material', 200)->nullable();
            $table->text('care_instructions')->nullable();
            
            $table->decimal('mrp', 12, 2);
            $table->decimal('selling_price', 12, 2);
            $table->decimal('cost_price', 12, 2)->nullable();
            
            $table->string('tax_category', 50)->default('standard');
            $table->decimal('gst_rate', 5, 2)->default(18.00);
            $table->string('hsn_code', 20)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new_arrival')->default(false);
            $table->boolean('is_bestseller')->default(false);
            $table->boolean('is_returnable')->default(true);
            $table->unsignedTinyInteger('return_window_days')->default(7);
            
            $table->decimal('avg_rating', 3, 2)->default(0.00);
            $table->unsignedInteger('total_reviews')->default(0);
            $table->unsignedInteger('total_sold')->default(0);
            
            $table->string('meta_title', 200)->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->string('meta_keywords', 300)->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Composite indexes
            $table->index(['category_id', 'is_active'], 'idx_products_category');
            $table->index(['is_active', 'deleted_at'], 'idx_products_active');
            $table->index(['is_featured', 'is_active'], 'idx_products_featured');
            $table->index(['is_new_arrival', 'is_active', 'created_at'], 'idx_products_new');
            $table->index(['is_bestseller', 'is_active'], 'idx_products_bestseller');
            $table->index('selling_price', 'idx_products_price');

            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->fullText(['name', 'short_description'], 'idx_products_search');
            } else {
                $table->index(['name', 'short_description'], 'idx_products_search');
            }
        });

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE products ADD CONSTRAINT chk_products_price CHECK (selling_price > 0 AND mrp >= selling_price)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
