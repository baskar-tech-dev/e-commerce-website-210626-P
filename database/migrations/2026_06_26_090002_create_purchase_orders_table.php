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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number', 50)->unique();
            $table->string('supplier_name', 200);
            $table->string('supplier_contact', 200)->nullable();
            $table->string('status', 30)->default('draft'); // draft, ordered, received, partial, cancelled
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->text('notes')->nullable();
            
            $table->timestamp('ordered_at')->nullable();
            $table->timestamp('received_at')->nullable();
            
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
