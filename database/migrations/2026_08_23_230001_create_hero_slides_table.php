<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('hero_slides')) {
            Schema::create('hero_slides', function (Blueprint $table) {
                $table->id();
                $table->string('tag')->nullable();
                $table->string('script_text')->nullable();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('left_image')->nullable();
                $table->string('right_image')->nullable();
                $table->string('button_text')->default('SHOP NOW');
                $table->string('button_link')->default('/shop');
                $table->integer('sort_order')->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed default boutique hero slides
            DB::table('hero_slides')->insert([
                [
                    'tag' => 'SIGNATURE STRETCHABLE BLOUSE',
                    'script_text' => 'Signature Craftsmanship',
                    'title' => 'Stretchable Bottle Green Blouse<br><span class="highlight">Peacock & Flute Art</span>',
                    'description' => 'Engineered with breathable 4-way cotton lycra, exquisite peacock embroidery, and seamless all-day comfort for silk sarees and festive occasions.',
                    'left_image' => '/asset/Bottle-Green-Designer-Stretchable-Blouse.jpeg',
                    'right_image' => '/asset/hero-bottle-green-sub.png',
                    'button_text' => 'SHOP THIS BLOUSE',
                    'button_link' => '/shop',
                    'sort_order' => 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tag' => 'FESTIVAL & WEDDING SPECIAL',
                    'script_text' => 'Royal Heritage',
                    'title' => 'Grand Festive Weaves<br><span class="highlight">& Bridal Blouses</span>',
                    'description' => 'Radiate timeless allure during weddings and celebrations with our rich zari borders and flawless fits.',
                    'left_image' => '/asset/banner-2-left.png',
                    'right_image' => '/asset/banner-2-right.png',
                    'button_text' => 'SHOP OCCASIONS',
                    'button_link' => '/shop',
                    'sort_order' => 2,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
