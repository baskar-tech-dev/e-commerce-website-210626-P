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
        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'module')) {
                $table->string('module', 50)->nullable()->after('name');
            }
            if (!Schema::hasColumn('permissions', 'action')) {
                $table->string('action', 50)->nullable()->after('module');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'action')) {
                $table->dropColumn('action');
            }
            if (Schema::hasColumn('permissions', 'module')) {
                $table->dropColumn('module');
            }
        });
    }
};
