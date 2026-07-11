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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_gateway', 30)->nullable()->after('payment_method');
            $table->string('razorpay_order_id', 255)->nullable()->after('payment_gateway');
            $table->string('razorpay_payment_id', 255)->nullable()->after('razorpay_order_id');
            $table->string('razorpay_signature', 500)->nullable()->after('razorpay_payment_id');
            $table->json('payment_response')->nullable()->after('razorpay_signature');
            $table->timestamp('paid_at')->nullable()->after('payment_response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_gateway',
                'razorpay_order_id',
                'razorpay_payment_id',
                'razorpay_signature',
                'payment_response',
                'paid_at'
            ]);
        });
    }
};
