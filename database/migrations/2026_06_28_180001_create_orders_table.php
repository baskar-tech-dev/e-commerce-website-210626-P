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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('order_number', 50)->unique();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('restrict');
            
            $table->string('status', 30)->default('pending');
            $table->string('payment_status', 30)->default('pending');
            $table->string('payment_method', 30)->nullable();
            
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('cgst_amount', 12, 2)->default(0.00);
            $table->decimal('sgst_amount', 12, 2)->default(0.00);
            $table->decimal('igst_amount', 12, 2)->default(0.00);
            $table->decimal('shipping_amount', 12, 2)->default(0.00);
            $table->decimal('grand_total', 12, 2);
            $table->unsignedInteger('total_items');
            $table->string('currency', 3)->default('INR');
            
            // Shipping address denormalized
            $table->string('shipping_first_name', 100);
            $table->string('shipping_last_name', 100);
            $table->string('shipping_phone', 20);
            $table->string('shipping_address_line_1', 255);
            $table->string('shipping_address_line_2', 255)->nullable();
            $table->string('shipping_city', 100);
            $table->string('shipping_state', 100);
            $table->string('shipping_postal_code', 20);
            $table->string('shipping_country', 2)->default('IN');
            
            // Billing address denormalized
            $table->string('billing_first_name', 100);
            $table->string('billing_last_name', 100);
            $table->string('billing_phone', 20);
            $table->string('billing_address_line_1', 255);
            $table->string('billing_address_line_2', 255)->nullable();
            $table->string('billing_city', 100);
            $table->string('billing_state', 100);
            $table->string('billing_postal_code', 20);
            $table->string('billing_country', 2)->default('IN');
            
            // Shipping details
            $table->string('shipping_method', 50)->nullable();
            $table->string('tracking_number', 100)->nullable();
            $table->string('courier_name', 100)->nullable();
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason', 500)->nullable();
            
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('user_id');
            $table->index('status');
            $table->index('payment_status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
