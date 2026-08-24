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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'badge')) {
                $table->string('badge', 60)->nullable()->after('is_bestseller');
            }
            if (!Schema::hasColumn('products', 'occasion')) {
                $table->string('occasion', 60)->nullable()->after('badge')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['occasion']);
            $table->dropColumn(['badge', 'occasion']);
        });
    }
};
