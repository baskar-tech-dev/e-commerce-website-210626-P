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
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('return_number', 30)->unique();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->onDelete('restrict');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('restrict');
            
            $table->string('status', 30)->default('requested'); // requested, approved, rejected, pickup_scheduled, picked_up, qc_passed, qc_failed, refunded
            $table->string('reason', 30); // wrong_size, defective, not_as_described, wrong_item, other
            $table->text('description')->nullable();
            $table->text('admin_notes')->nullable();
            $table->decimal('refund_amount', 12, 2)->nullable();
            
            $table->timestamp('pickup_scheduled_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('qc_completed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('rejection_reason', 500)->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('order_id');
            $table->index('user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
