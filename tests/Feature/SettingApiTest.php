<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::create([
            'group' => 'general',
            'key' => 'store_name',
            'value' => 'Vibe Original',
            'type' => 'text',
        ]);

        Setting::create([
            'group' => 'payment',
            'key' => 'cod_active',
            'value' => '1',
            'type' => 'boolean',
        ]);
    }

    public function test_can_retrieve_grouped_settings(): void
    {
        $response = $this->getJson('/api/admin/settings');
        
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.general.store_name', 'Vibe Original')
            ->assertJsonPath('data.payment.cod_active', true);
    }

    public function test_can_batch_update_settings(): void
    {
        $response = $this->postJson('/api/admin/settings/batch', [
            'settings' => [
                'general' => [
                    'store_name' => 'Vibe New Store Name',
                    'currency' => 'INR',
                ],
                'payment' => [
                    'cod_active' => false,
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Verify changes are saved in DB
        $this->assertEquals('Vibe New Store Name', Setting::get('store_name', 'general'));
        $this->assertEquals('INR', Setting::get('currency', 'general'));
        $this->assertFalse(Setting::get('cod_active', 'payment'));
    }
}
