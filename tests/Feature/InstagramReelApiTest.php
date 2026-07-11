<?php

namespace Tests\Feature;

use App\Models\InstagramReel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstagramReelApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_reels(): void
    {
        InstagramReel::create([
            'caption' => 'Video 1',
            'type' => 'file',
            'video_url' => '/storage/reels/test1.mp4',
            'sort_order' => 10,
        ]);

        InstagramReel::create([
            'caption' => 'Video 2',
            'type' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'sort_order' => 20,
        ]);

        $response = $this->getJson('/api/admin/instagram-reels');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_can_create_reel(): void
    {
        $response = $this->postJson('/api/admin/instagram-reels', [
            'caption' => 'New Festive Blouse Showcase',
            'type' => 'file',
            'video_url' => '/storage/reels/new.mp4',
            'instagram_url' => 'https://www.instagram.com/reel/xyz123/',
            'sort_order' => 5,
            'is_active' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.caption', 'New Festive Blouse Showcase');

        $this->assertDatabaseHas('instagram_reels', [
            'caption' => 'New Festive Blouse Showcase',
            'video_url' => '/storage/reels/new.mp4',
        ]);
    }

    public function test_admin_can_update_reel(): void
    {
        $reel = InstagramReel::create([
            'caption' => 'Old Caption',
            'type' => 'file',
            'video_url' => '/storage/reels/old.mp4',
            'sort_order' => 10,
        ]);

        $response = $this->putJson("/api/admin/instagram-reels/{$reel->id}", [
            'caption' => 'Updated Caption',
            'video_url' => '/storage/reels/new.mp4',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.caption', 'Updated Caption');

        $this->assertDatabaseHas('instagram_reels', [
            'id' => $reel->id,
            'caption' => 'Updated Caption',
            'video_url' => '/storage/reels/new.mp4',
        ]);
    }

    public function test_admin_can_delete_reel(): void
    {
        $reel = InstagramReel::create([
            'caption' => 'To be deleted',
            'type' => 'youtube',
            'video_url' => 'https://www.youtube.com/shorts/q2ZgA7Xb79U',
            'sort_order' => 10,
        ]);

        $response = $this->deleteJson("/api/admin/instagram-reels/{$reel->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('instagram_reels', ['id' => $reel->id]);
    }

    public function test_storefront_can_list_reels(): void
    {
        // 1 active, 1 inactive
        InstagramReel::create([
            'caption' => 'Active Video',
            'type' => 'file',
            'video_url' => '/storage/reels/active.mp4',
            'is_active' => true,
            'sort_order' => 10,
        ]);

        InstagramReel::create([
            'caption' => 'Inactive Video',
            'type' => 'file',
            'video_url' => '/storage/reels/inactive.mp4',
            'is_active' => false,
            'sort_order' => 20,
        ]);

        $response = $this->getJson('/api/storefront/instagram-reels');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.caption', 'Active Video');
    }
}
