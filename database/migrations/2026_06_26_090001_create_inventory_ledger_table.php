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
        Schema::create('inventory_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')
                ->constrained('product_variants')
                ->onDelete('restrict');
            
            $table->string('type', 30); // PURCHASE, SALE, RETURN, DAMAGE, ADJUSTMENT, TRANSFER, RESERVATION, RELEASE
            $table->string('direction', 3); // IN or OUT
            $table->unsignedInteger('quantity');
            $table->decimal('unit_cost', 12, 2)->nullable();
            
            // Polymorphic relation
            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            
            $table->unsignedInteger('stock_before');
            $table->unsignedInteger('stock_after');
            
            $table->string('notes', 500)->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('product_variant_id');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_ledger');
    }
};
