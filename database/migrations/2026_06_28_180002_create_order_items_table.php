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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->onDelete('cascade');
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('restrict');
            $table->foreignId('product_variant_id')
                ->constrained('product_variants')
                ->onDelete('restrict');
            
            $table->string('product_name', 300);
            $table->string('variant_name', 100)->nullable();
            $table->string('sku', 100);
            
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('unit_mrp', 12, 2);
            $table->decimal('discount', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(18.00);
            $table->decimal('total_price', 12, 2);
            $table->string('status', 30)->default('active'); // active, cancelled, returned
            
            $table->timestamps();
            
            // Indexes
            $table->index('order_id');
            $table->index('product_variant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
