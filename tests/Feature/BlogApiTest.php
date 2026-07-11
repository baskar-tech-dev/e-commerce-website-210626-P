<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogApiTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $category;
    protected $tag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
        ]);

        $this->category = BlogCategory::create([
            'name' => 'Fashion Styles',
            'slug' => 'fashion-styles',
        ]);

        $this->tag = BlogTag::create([
            'name' => 'Summer Wear',
            'slug' => 'summer-wear',
        ]);
    }

    public function test_can_list_blog_categories(): void
    {
        $response = $this->getJson('/api/admin/blog/categories');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_create_blog_category(): void
    {
        $response = $this->postJson('/api/admin/blog/categories', [
            'name' => 'New Season Trends',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', 'new-season-trends');
    }

    public function test_can_list_blog_tags(): void
    {
        $response = $this->getJson('/api/admin/blog/tags');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_create_blog_tag(): void
    {
        $response = $this->postJson('/api/admin/blog/tags', [
            'name' => 'Formal Accessories',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', 'formal-accessories');
    }

    public function test_can_list_posts_with_filters(): void
    {
        $post = BlogPost::create([
            'blog_category_id' => $this->category->id,
            'title' => 'Top Linen Wear Guide',
            'content' => '<p>Pure linen style guide.</p>',
            'status' => 'published',
        ]);

        $response = $this->getJson('/api/admin/blog/posts');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');

        // Test search
        $response = $this->getJson('/api/admin/blog/posts?search=Linen');
        $response->assertStatus(200)->assertJsonCount(1, 'data');

        $response = $this->getJson('/api/admin/blog/posts?search=Cotton');
        $response->assertStatus(200)->assertJsonCount(0, 'data');
    }

    public function test_can_create_blog_post(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson('/api/admin/blog/posts', [
            'blog_category_id' => $this->category->id,
            'title' => 'Linen Suits Style Guide',
            'content' => '<p>How to style linen suits comfortably.</p>',
            'status' => 'published',
            'tags' => [$this->tag->id],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', 'linen-suits-style-guide');

        $this->assertDatabaseHas('blog_posts', [
            'title' => 'Linen Suits Style Guide',
            'status' => 'published',
            'author_id' => $this->adminUser->id,
        ]);

        $this->assertDatabaseHas('blog_post_tag', [
            'blog_tag_id' => $this->tag->id,
        ]);
    }

    public function test_can_update_blog_post(): void
    {
        $post = BlogPost::create([
            'blog_category_id' => $this->category->id,
            'title' => 'Linen Suits Style Guide',
            'slug' => 'linen-suits-style-guide',
            'content' => '<p>Old content.</p>',
            'status' => 'draft',
        ]);

        $response = $this->putJson("/api/admin/blog/posts/{$post->id}", [
            'blog_category_id' => $this->category->id,
            'title' => 'Linen Suits Style Guide Updated',
            'slug' => 'linen-suits-style-guide',
            'content' => '<p>New updated content body.</p>',
            'status' => 'published',
            'tags' => [$this->tag->id],
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Linen Suits Style Guide Updated', $post->fresh()->title);
        $this->assertEquals('published', $post->fresh()->status);
        $this->assertNotNull($post->fresh()->published_at);
    }

    public function test_can_delete_blog_post(): void
    {
        $post = BlogPost::create([
            'blog_category_id' => $this->category->id,
            'title' => 'Short Blazer Styles',
            'content' => '<p>Style guide blazers.</p>',
            'status' => 'draft',
        ]);

        $response = $this->deleteJson("/api/admin/blog/posts/{$post->id}");
        $response->assertStatus(200);

        $this->assertSoftDeleted('blog_posts', [
            'id' => $post->id,
        ]);
    }
}
