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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('payment_id')
                ->constrained('payments')
                ->onDelete('restrict');
            $table->foreignId('order_id')
                ->constrained('orders')
                ->onDelete('restrict');
            $table->foreignId('return_id')
                ->nullable()
                ->constrained('returns')
                ->onDelete('set null');
            
            $table->string('gateway_refund_id', 255)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('reason', 500);
            $table->string('status', 30)->default('pending'); // pending, processed, failed
            $table->timestamp('processed_at')->nullable();
            $table->json('gateway_response')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('payment_id');
            $table->index('order_id');
            $table->index('return_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
