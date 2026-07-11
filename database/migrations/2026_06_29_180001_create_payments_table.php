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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->onDelete('restrict');
            $table->string('gateway', 30); // razorpay, stripe, cod
            $table->string('gateway_payment_id', 255)->nullable();
            $table->string('gateway_order_id', 255)->nullable();
            $table->string('gateway_signature', 500)->nullable();
            $table->string('method', 30); // upi, credit_card, debit_card, net_banking, cod, wallet
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('INR');
            $table->string('status', 30)->default('pending'); // pending, authorized, captured, failed, refunded
            $table->string('failure_reason', 500)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('gateway_response')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('order_id');
            $table->index('gateway_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
