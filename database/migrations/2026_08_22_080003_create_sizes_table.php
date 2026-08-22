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
        Schema::create('sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('size_group_id')->constrained('size_groups')->onDelete('cascade');
            $table->string('name', 50); // e.g. "32-34", "34-37", "S", "M"
            $table->string('code', 50)->nullable(); // e.g. "3234", "S"
            $table->string('measurement_hint', 150)->nullable(); // e.g. "Fits bust 32-34 inches"
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sizes');
    }
};
