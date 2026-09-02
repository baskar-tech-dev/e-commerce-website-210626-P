<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_with_valid_details(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Priya Sundaram',
            'email' => 'priya@example.com',
            'phone' => '9944285102',
            'password' => 'SecretPass123',
            'password_confirmation' => 'SecretPass123',
            'terms' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('user.email', 'priya@example.com')
            ->assertJsonPath('user.first_name', 'Priya')
            ->assertJsonPath('user.last_name', 'Sundaram')
            ->assertJsonStructure(['access_token', 'token_type', 'user']);

        $this->assertDatabaseHas('users', [
            'email' => 'priya@example.com',
            'phone' => '9944285102',
        ]);

        $user = User::where('email', 'priya@example.com')->first();
        $this->assertNotNull($user->customerProfile);
    }

    public function test_customer_registration_normalizes_phone_with_country_code(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Kavitha Nathan',
            'email' => 'kavitha@example.com',
            'phone' => '+91 98765 43210',
            'password' => 'SecretPass123',
            'password_confirmation' => 'SecretPass123',
            'terms' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', [
            'email' => 'kavitha@example.com',
            'phone' => '9876543210',
        ]);
    }

    public function test_registration_fails_when_phone_is_missing(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Priya Sundaram',
            'email' => 'priya2@example.com',
            'password' => 'SecretPass123',
            'password_confirmation' => 'SecretPass123',
            'terms' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_registration_fails_when_phone_is_not_10_digits(): void
    {
        // 8 digits
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Priya Sundaram',
            'email' => 'priya3@example.com',
            'phone' => '99442851',
            'password' => 'SecretPass123',
            'password_confirmation' => 'SecretPass123',
            'terms' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_registration_fails_when_phone_starts_with_invalid_prefix(): void
    {
        // 10 digits starting with 1
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Priya Sundaram',
            'email' => 'priya4@example.com',
            'phone' => '1234567890',
            'password' => 'SecretPass123',
            'password_confirmation' => 'SecretPass123',
            'terms' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_registration_fails_on_duplicate_phone(): void
    {
        User::create([
            'name' => 'Existing User',
            'email' => 'existing2@example.com',
            'phone' => '9944285102',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'phone' => '9944285102',
            'password' => 'SecretPass123',
            'password_confirmation' => 'SecretPass123',
            'terms' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_registration_fails_on_duplicate_email(): void
    {
        User::create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'phone' => '9111222333',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'phone' => '9444555666',
            'password' => 'SecretPass123',
            'password_confirmation' => 'SecretPass123',
            'terms' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_customer_can_login_with_email_or_phone(): void
    {
        $user = User::create([
            'name' => 'Anitha Kumar',
            'first_name' => 'Anitha',
            'last_name' => 'Kumar',
            'email' => 'anitha@example.com',
            'phone' => '9876543210',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        // Login using Email
        $resEmail = $this->postJson('/api/auth/login', [
            'email' => 'anitha@example.com',
            'password' => 'password123',
        ]);

        $resEmail->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('user.email', 'anitha@example.com');

        // Login using Phone Number
        $resPhone = $this->postJson('/api/auth/login', [
            'email' => '9876543210',
            'password' => 'password123',
        ]);

        $resPhone->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('user.email', 'anitha@example.com');
    }

    public function test_customer_login_fails_with_invalid_credentials(): void
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('correct_password'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_customer_can_request_forgot_password(): void
    {
        User::create([
            'name' => 'Reset User',
            'email' => 'reset@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'reset@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_authenticated_customer_can_logout(): void
    {
        $user = User::create([
            'name' => 'Logout User',
            'email' => 'logout@example.com',
            'password' => Hash::make('password'),
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
