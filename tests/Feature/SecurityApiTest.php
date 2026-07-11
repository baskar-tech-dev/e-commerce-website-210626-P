<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SecurityApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_responses_contain_required_security_headers(): void
    {
        $response = $this->getJson('/api/storefront/products');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->assertHeader('Content-Security-Policy');
        $response->assertHeader('X-Request-Id');
    }

    public function test_api_rate_limiting_enforces_limits(): void
    {
        // Limit is 60 per minute for public_api
        for ($i = 0; $i < 60; $i++) {
            $response = $this->getJson('/api/storefront/products');
            if ($response->status() === 429) {
                break;
            }
        }

        // The next request must trigger a 429 Too Many Requests response
        $response = $this->getJson('/api/storefront/products');
        $response->assertStatus(429);
    }
}
