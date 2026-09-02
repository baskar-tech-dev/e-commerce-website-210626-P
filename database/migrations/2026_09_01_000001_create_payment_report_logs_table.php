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
        Schema::create('payment_report_logs', function (Blueprint $table) {
            $table->id();
            $table->date('report_date')->index();
            $table->dateTime('triggered_at')->index();
            $table->string('status', 20)->default('success')->index(); // 'success', 'failed', 'skipped'
            $table->string('channel', 30)->default('scheduled')->index(); // 'scheduled', 'cli', 'admin_api'
            $table->json('recipients')->nullable();
            $table->decimal('gross_revenue', 12, 2)->default(0.00);
            $table->decimal('online_collection', 12, 2)->default(0.00);
            $table->decimal('cod_amount', 12, 2)->default(0.00);
            $table->decimal('net_settled', 12, 2)->default(0.00);
            $table->decimal('gateway_fee', 12, 2)->default(0.00);
            $table->decimal('gateway_tax', 12, 2)->default(0.00);
            $table->unsignedInteger('orders_count')->default(0);
            $table->unsignedInteger('transactions_count')->default(0);
            $table->string('settlement_utr', 100)->nullable();
            $table->string('settlement_status', 30)->nullable();
            $table->json('summary_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_report_logs');
    }
};
