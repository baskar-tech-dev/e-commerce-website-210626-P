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
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('blog_category_id')
                ->nullable()
                ->constrained('blog_categories')
                ->onDelete('set null');
                
            $table->foreignId('author_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->string('title', 300);
            $table->string('slug', 350)->unique();
            $table->string('excerpt', 500)->nullable();
            $table->longText('content');
            $table->string('featured_image', 500)->nullable();
            $table->string('status', 20)->default('draft'); // draft, published, archived
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            
            $table->string('meta_title', 200)->nullable();
            $table->string('meta_description', 500)->nullable();
            
            $table->timestamp('published_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
