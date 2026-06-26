<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_categories(): void
    {
        Category::factory()->create(['name' => 'Men', 'slug' => 'men']);
        Category::factory()->create(['name' => 'Women', 'slug' => 'women']);

        $response = $this->getJson('/api/admin/categories');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_create_category_with_auto_slug(): void
    {
        $response = $this->postJson('/api/admin/categories', [
            'name' => 'Fancy Shoes',
            'description' => 'Test description'
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Fancy Shoes')
            ->assertJsonPath('data.slug', 'fancy-shoes');

        $this->assertDatabaseHas('categories', [
            'name' => 'Fancy Shoes',
            'slug' => 'fancy-shoes'
        ]);
    }

    public function test_can_create_child_category(): void
    {
        $parent = Category::factory()->create(['name' => 'Apparel', 'slug' => 'apparel']);

        $response = $this->postJson('/api/admin/categories', [
            'name' => 'Shirts',
            'parent_id' => $parent->id
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.parent_id', $parent->id);

        $this->assertDatabaseHas('categories', [
            'name' => 'Shirts',
            'parent_id' => $parent->id
        ]);
    }

    public function test_enforces_slug_uniqueness_by_appending_counter(): void
    {
        Category::factory()->create(['name' => 'T-Shirts', 'slug' => 't-shirts']);

        $response = $this->postJson('/api/admin/categories', [
            'name' => 'T-Shirts'
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.slug', 't-shirts-1');
    }

    public function test_cannot_delete_category_with_children(): void
    {
        $parent = Category::factory()->create(['name' => 'Parent', 'slug' => 'parent']);
        Category::factory()->create(['name' => 'Child', 'parent_id' => $parent->id, 'slug' => 'child']);

        $response = $this->deleteJson("/api/admin/categories/{$parent->id}");

        $response->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error_code', 'CONFLICT');

        $this->assertDatabaseHas('categories', ['id' => $parent->id]);
    }

    public function test_can_delete_empty_category(): void
    {
        $category = Category::factory()->create(['name' => 'Empty', 'slug' => 'empty']);

        $response = $this->deleteJson("/api/admin/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Soft deletes
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }
}
