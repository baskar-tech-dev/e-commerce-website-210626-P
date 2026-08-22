<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_gateway')) {
                $table->string('payment_gateway', 30)->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('orders', 'gateway_order_id')) {
                $table->string('gateway_order_id', 255)->nullable()->after('payment_gateway');
            }
            if (!Schema::hasColumn('orders', 'gateway_payment_id')) {
                $table->string('gateway_payment_id', 255)->nullable()->after('gateway_order_id');
            }
            if (!Schema::hasColumn('orders', 'gateway_signature')) {
                $table->string('gateway_signature', 500)->nullable()->after('gateway_payment_id');
            }
            if (!Schema::hasColumn('orders', 'payment_response')) {
                $table->json('payment_response')->nullable()->after('gateway_signature');
            }
            if (!Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_response');
            }
        });

        // Migrate existing legacy razorpay fields if they exist
        if (Schema::hasColumn('orders', 'razorpay_order_id')) {
            DB::statement("UPDATE orders SET gateway_order_id = razorpay_order_id WHERE gateway_order_id IS NULL AND razorpay_order_id IS NOT NULL");
        }
        if (Schema::hasColumn('orders', 'razorpay_payment_id')) {
            DB::statement("UPDATE orders SET gateway_payment_id = razorpay_payment_id WHERE gateway_payment_id IS NULL AND razorpay_payment_id IS NOT NULL");
        }
        if (Schema::hasColumn('orders', 'razorpay_signature')) {
            DB::statement("UPDATE orders SET gateway_signature = razorpay_signature WHERE gateway_signature IS NULL AND razorpay_signature IS NOT NULL");
        }

        // Drop obsolete razorpay columns from orders
        Schema::table('orders', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('orders', 'razorpay_order_id')) {
                $columnsToDrop[] = 'razorpay_order_id';
            }
            if (Schema::hasColumn('orders', 'razorpay_payment_id')) {
                $columnsToDrop[] = 'razorpay_payment_id';
            }
            if (Schema::hasColumn('orders', 'razorpay_signature')) {
                $columnsToDrop[] = 'razorpay_signature';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('razorpay_order_id', 255)->nullable()->after('payment_gateway');
            $table->string('razorpay_payment_id', 255)->nullable()->after('razorpay_order_id');
            $table->string('razorpay_signature', 500)->nullable()->after('razorpay_payment_id');
            $table->dropColumn(['gateway_order_id', 'gateway_payment_id', 'gateway_signature']);
        });
    }
};
