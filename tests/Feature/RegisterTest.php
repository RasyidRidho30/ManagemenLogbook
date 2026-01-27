<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_endpoint_creates_user()
    {
        // This application uses stored procedures (MySQL). Skip if MySQL isn't available.
        if (config('database.default') !== 'mysql' || !extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('MySQL/pdo_mysql is not available; skipping register test that depends on stored procedures.');
        }

        $payload = [
            'username' => 'testuser',
            'email' => 'testuser@example.test',
            'password' => 'secret123',
            'first_name' => 'Test',
            'last_name' => 'User'
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'usr_id']);
    }
}
