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
        Schema::create('stock_inwards', function (Blueprint $table) {
            $table->id();
            $table->string('inward_number', 60)->unique();
            $table->date('inward_date');
            $table->string('supplier_name', 150)->nullable();
            $table->string('reference_no', 100)->nullable();
            $table->string('status', 30)->default('completed');
            $table->integer('total_items')->default(0);
            $table->integer('total_quantity')->default(0);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_inward_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_inward_id')->constrained('stock_inwards')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('color', 80)->nullable();
            $table->foreignId('color_id')->nullable()->constrained('colors')->nullOnDelete();
            $table->string('size', 50)->nullable();
            $table->string('sku', 100)->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_cost', 10, 2)->default(0.00);
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_inward_items');
        Schema::dropIfExists('stock_inwards');
    }
};
