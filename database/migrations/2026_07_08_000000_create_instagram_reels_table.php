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
        Schema::create('instagram_reels', function (Blueprint $table) {
            $table->id();
            $table->string('caption')->nullable();
            $table->string('type', 20)->default('file'); // 'file' (direct mp4/webm/etc) or 'youtube'
            $table->text('video_url'); // path to uploaded file or external direct/youtube link
            $table->text('instagram_url')->nullable(); // link to social media post
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
        Schema::dropIfExists('instagram_reels');
    }
};
