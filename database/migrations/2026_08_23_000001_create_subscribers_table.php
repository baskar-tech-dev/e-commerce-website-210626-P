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
        if (!Schema::hasTable('subscribers')) {
            Schema::create('subscribers', function (Blueprint $table) {
                $table->id();
                $table->string('phone', 30)->nullable()->index();
                $table->string('email', 150)->nullable()->index();
                $table->string('source', 50)->default('whatsapp_club'); // whatsapp_club, newsletter, footer, etc.
                $table->string('status', 20)->default('active');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscribers');
    }
};
