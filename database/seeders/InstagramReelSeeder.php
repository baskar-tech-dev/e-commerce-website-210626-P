<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InstagramReel;

class InstagramReelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        InstagramReel::truncate();

        $reels = [
            [
                'caption' => 'Vibrant Summer Kurtis Collection',
                'type' => 'file',
                'video_url' => 'https://assets.mixkit.co/videos/preview/mixkit-fashion-woman-with-silver-glitter-makeup-40177-large.mp4',
                'instagram_url' => 'https://www.instagram.com/reel/C3B4d5e6f7g/',
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'caption' => 'Pure Zari Silk Sarees Exhibition',
                'type' => 'youtube',
                'video_url' => 'https://www.youtube.com/shorts/q2ZgA7Xb79U',
                'instagram_url' => 'https://www.youtube.com/shorts/q2ZgA7Xb79U',
                'is_active' => true,
                'sort_order' => 20,
            ],
            [
                'caption' => 'Stitchable Festive Designer Blouses',
                'type' => 'file',
                'video_url' => 'https://assets.mixkit.co/videos/preview/mixkit-beautiful-woman-posing-with-a-red-light-40175-large.mp4',
                'instagram_url' => 'https://www.instagram.com/reel/C3B4d5e6f7g/',
                'is_active' => true,
                'sort_order' => 30,
            ],
            [
                'caption' => 'Handcrafted Traditional Weaving Process',
                'type' => 'youtube',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'instagram_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'is_active' => true,
                'sort_order' => 40,
            ],
        ];

        foreach ($reels as $reel) {
            InstagramReel::create($reel);
        }
    }
}
