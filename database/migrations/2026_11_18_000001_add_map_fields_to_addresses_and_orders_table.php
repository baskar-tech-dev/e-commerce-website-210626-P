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
        if (Schema::hasTable('addresses')) {
            Schema::table('addresses', function (Blueprint $table) {
                if (!Schema::hasColumn('addresses', 'map_url')) {
                    $table->text('map_url')->nullable()->after('landmark');
                }
                if (!Schema::hasColumn('addresses', 'latitude')) {
                    $table->decimal('latitude', 10, 8)->nullable()->after('map_url');
                }
                if (!Schema::hasColumn('addresses', 'longitude')) {
                    $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'shipping_map_url')) {
                    $table->text('shipping_map_url')->nullable()->after('shipping_country');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('addresses')) {
            Schema::table('addresses', function (Blueprint $table) {
                if (Schema::hasColumn('addresses', 'longitude')) {
                    $table->dropColumn('longitude');
                }
                if (Schema::hasColumn('addresses', 'latitude')) {
                    $table->dropColumn('latitude');
                }
                if (Schema::hasColumn('addresses', 'map_url')) {
                    $table->dropColumn('map_url');
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'shipping_map_url')) {
                    $table->dropColumn('shipping_map_url');
                }
            });
        }
    }
};
