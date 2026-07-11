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
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')
                ->constrained('returns')
                ->onDelete('cascade');
            $table->foreignId('order_item_id')
                ->constrained('order_items')
                ->onDelete('restrict');
            $table->foreignId('product_variant_id')
                ->constrained('product_variants')
                ->onDelete('restrict');
            
            $table->unsignedInteger('quantity');
            $table->string('reason', 30);
            $table->json('photos')->nullable();
            $table->string('qc_status', 30)->nullable(); // passed, failed
            $table->string('qc_notes', 500)->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('return_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_items');
    }
};
