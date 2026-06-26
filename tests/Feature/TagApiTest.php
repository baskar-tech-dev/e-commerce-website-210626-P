<?php

namespace Tests\Feature;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_tags(): void
    {
        Tag::factory()->create(['name' => 'Cotton', 'slug' => 'cotton']);
        Tag::factory()->create(['name' => 'Wool', 'slug' => 'wool']);

        $response = $this->getJson('/api/admin/tags');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_create_tag_with_auto_slug(): void
    {
        $response = $this->postJson('/api/admin/tags', [
            'name' => 'Formal Wear'
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Formal Wear')
            ->assertJsonPath('data.slug', 'formal-wear');

        $this->assertDatabaseHas('tags', [
            'name' => 'Formal Wear',
            'slug' => 'formal-wear'
        ]);
    }

    public function test_can_update_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'Old Tag', 'slug' => 'old-tag']);

        $response = $this->putJson("/api/admin/tags/{$tag->id}", [
            'name' => 'New Tag'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'New Tag')
            ->assertJsonPath('data.slug', 'new-tag');
    }

    public function test_can_delete_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'Temporary', 'slug' => 'temporary']);

        $response = $this->deleteJson("/api/admin/tags/{$tag->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }
}
