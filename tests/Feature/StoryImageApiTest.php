<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StoryImageApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $regularStaff;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin'], ['description' => 'Super Admin']);
        $staffRole = Role::firstOrCreate(['name' => 'staff'], ['description' => 'Staff Member']);
        $customerRole = Role::firstOrCreate(['name' => 'customer'], ['description' => 'Customer']);

        $this->superAdmin = User::factory()->create([
            'email' => 'superadmin@example.com',
            'role_id' => 1,
        ]);
        $this->superAdmin->roles()->sync([$superAdminRole->id]);

        $this->regularStaff = User::factory()->create([
            'email' => 'staff@example.com',
            'role_id' => 7,
        ]);
        $this->regularStaff->roles()->sync([$staffRole->id]);

        $this->customer = User::factory()->create([
            'email' => 'customer@example.com',
            'role_id' => 6,
        ]);
        $this->customer->roles()->sync([$customerRole->id]);
    }

    public function test_public_can_read_story_settings(): void
    {
        $response = $this->getJson('/api/storefront/story-settings');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.story_image', '/asset/mother.jpg')
            ->assertJsonPath('data.default_image', '/asset/mother.jpg');
    }

    public function test_unauthenticated_user_cannot_update_or_upload_story_image(): void
    {
        $res1 = $this->postJson('/api/admin/story-image/update', ['image_url' => 'https://example.com/test.jpg']);
        $res1->assertStatus(401);

        $res2 = $this->postJson('/api/admin/story-image/upload');
        $res2->assertStatus(401);
    }

    public function test_customer_cannot_modify_story_image(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->postJson('/api/admin/story-image/update', ['image_url' => 'https://example.com/test.jpg']);
        $response->assertStatus(403);
    }

    public function test_regular_staff_cannot_modify_story_image(): void
    {
        Sanctum::actingAs($this->regularStaff);

        $response = $this->postJson('/api/admin/story-image/update', ['image_url' => 'https://example.com/test.jpg']);
        $response->assertStatus(403);
    }

    public function test_super_admin_can_update_story_image_url(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $testUrl = 'https://example.com/images/new_founder.jpg';
        $response = $this->postJson('/api/admin/story-image/update', [
            'image_url' => $testUrl,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.story_image', $testUrl)
            ->assertJsonPath('data.is_custom', true);

        // Verify storefront sees the updated image
        $storefrontRes = $this->getJson('/api/storefront/story-settings');
        $storefrontRes->assertStatus(200)
            ->assertJsonPath('data.story_image', $testUrl);
    }

    public function test_super_admin_can_reset_story_image_to_default(): void
    {
        Sanctum::actingAs($this->superAdmin);

        // Set custom first
        Setting::set('story_image', 'https://example.com/custom.jpg', 'homepage');

        // Reset
        $response = $this->postJson('/api/admin/story-image/update', [
            'reset' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.story_image', '/asset/mother.jpg')
            ->assertJsonPath('data.is_custom', false);
    }

    public function test_super_admin_can_upload_story_image(): void
    {
        Storage::fake('public');
        Sanctum::actingAs($this->superAdmin);

        $fakeFile = UploadedFile::fake()->image('founder_portrait.jpg', 800, 1000);

        $response = $this->postJson('/api/admin/story-image/upload', [
            'image' => $fakeFile,
            'fit' => 'contain',
            'position' => 'center',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_custom', true)
            ->assertJsonPath('data.story_image_fit', 'contain');

        $newUrl = $response->json('data.story_image');
        $this->assertNotEmpty($newUrl);
        $this->assertStringContainsString('story_', $newUrl);
    }
}
