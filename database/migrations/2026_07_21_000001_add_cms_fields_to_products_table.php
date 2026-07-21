<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('collection_name', 150)->nullable()->after('category_id');
            $table->string('display_name', 300)->nullable()->after('name');
            $table->string('subtitle', 300)->nullable()->after('display_name');
            $table->string('occasion', 100)->nullable()->after('short_description');
            $table->string('season', 100)->nullable()->after('occasion');
            $table->string('style', 100)->nullable()->after('season');
            $table->string('fabric', 150)->nullable()->after('material');
            $table->string('pattern', 100)->nullable()->after('fabric');
            $table->string('sleeve', 100)->nullable()->after('pattern');
            $table->string('neck', 100)->nullable()->after('sleeve');
            $table->string('fit', 100)->nullable()->after('neck');
            $table->string('alt_text', 300)->nullable()->after('care_instructions');
            $table->boolean('is_premium')->default(false)->after('is_bestseller');
            $table->boolean('is_trending')->default(false)->after('is_premium');
            $table->boolean('is_editors_pick')->default(false)->after('is_trending');
            $table->integer('sort_order')->default(0)->after('is_editors_pick');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'collection_name',
                'display_name',
                'subtitle',
                'occasion',
                'season',
                'style',
                'fabric',
                'pattern',
                'sleeve',
                'neck',
                'fit',
                'alt_text',
                'is_premium',
                'is_trending',
                'is_editors_pick',
                'sort_order',
            ]);
        });
    }
};
