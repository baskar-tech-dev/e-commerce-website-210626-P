<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE products MODIFY COLUMN badge VARCHAR(255) NULL");
            DB::statement("ALTER TABLE products MODIFY COLUMN occasion VARCHAR(255) NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE products MODIFY COLUMN badge VARCHAR(60) NULL");
            DB::statement("ALTER TABLE products MODIFY COLUMN occasion VARCHAR(60) NULL");
        }
    }
};
