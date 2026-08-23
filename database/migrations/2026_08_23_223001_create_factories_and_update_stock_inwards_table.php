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
        // 1. Create factories master table
        if (!Schema::hasTable('factories')) {
            Schema::create('factories', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->string('code', 50)->nullable()->unique();
                $table->string('contact_person', 100)->nullable();
                $table->string('phone', 30)->nullable();
                $table->string('email', 100)->nullable();
                $table->text('address')->nullable();
                $table->string('city', 80)->nullable();
                $table->string('state', 80)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. Add category_id and factory_id to stock_inwards table
        Schema::table('stock_inwards', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_inwards', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('inward_date')->constrained('categories')->nullOnDelete();
            }
            if (!Schema::hasColumn('stock_inwards', 'factory_id')) {
                $table->foreignId('factory_id')->nullable()->after('category_id')->constrained('factories')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_inwards', function (Blueprint $table) {
            if (Schema::hasColumn('stock_inwards', 'factory_id')) {
                $table->dropConstrainedForeignId('factory_id');
            }
            if (Schema::hasColumn('stock_inwards', 'category_id')) {
                $table->dropConstrainedForeignId('category_id');
            }
        });

        Schema::dropIfExists('factories');
    }
};
