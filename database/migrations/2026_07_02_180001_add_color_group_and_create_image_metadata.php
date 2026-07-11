<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->string('color_group', 50)->nullable()->after('variant_id');
        });

        Schema::create('image_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_image_id')
                ->constrained('product_images')
                ->onDelete('cascade');
            
            $table->integer('width');
            $table->integer('height');
            $table->integer('file_size');
            $table->string('format', 20);
            $table->string('color_space', 20)->nullable();
            $table->integer('dpi')->nullable();
            $table->string('compression_ratio', 20)->nullable();
            $table->string('hash', 64);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('image_metadata');
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn('color_group');
        });
    }
};
