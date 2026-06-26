<?php

namespace Tests\Feature;

use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_brands(): void
    {
        Brand::factory()->create(['name' => 'Nike', 'slug' => 'nike']);
        Brand::factory()->create(['name' => 'Zara', 'slug' => 'zara']);

        $response = $this->getJson('/api/admin/brands');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_create_brand_with_auto_slug(): void
    {
        $response = $this->postJson('/api/admin/brands', [
            'name' => 'Puma Apparel',
            'website' => 'https://puma.com'
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Puma Apparel')
            ->assertJsonPath('data.slug', 'puma-apparel');

        $this->assertDatabaseHas('brands', [
            'name' => 'Puma Apparel',
            'slug' => 'puma-apparel'
        ]);
    }

    public function test_can_update_brand(): void
    {
        $brand = Brand::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

        $response = $this->putJson("/api/admin/brands/{$brand->id}", [
            'name' => 'New Name'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.slug', 'new-name');
    }

    public function test_can_delete_brand(): void
    {
        $brand = Brand::factory()->create(['name' => 'Temporary', 'slug' => 'temporary']);

        $response = $this->deleteJson("/api/admin/brands/{$brand->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('brands', ['id' => $brand->id]);
    }
}
