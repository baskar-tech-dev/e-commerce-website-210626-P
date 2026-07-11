<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->string('description', 500)->nullable();
            $table->string('type', 30); // percentage, flat, free_shipping
            $table->decimal('value', 12, 2);
            $table->decimal('max_discount', 12, 2)->nullable();
            $table->decimal('min_order_value', 12, 2)->nullable();
            $table->unsignedInteger('max_uses_total')->nullable();
            $table->unsignedInteger('max_uses_per_user')->default(1);
            $table->unsignedInteger('times_used')->default(0);
            $table->json('applicable_category_ids')->nullable();
            $table->json('applicable_product_ids')->nullable();
            $table->json('excluded_product_ids')->nullable();
            $table->boolean('first_order_only')->default(false);
            $table->boolean('is_auto_apply')->default(false);
            $table->boolean('is_combinable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at');
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('code');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
