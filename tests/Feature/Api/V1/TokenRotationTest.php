<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenRotationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    /** @test */
    public function token_is_rotated_on_authenticated_request()
    {
        // Login to get initial token
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $loginResponse->assertStatus(200);
        $initialToken = $loginResponse->json('data.token');

        // Make authenticated request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$initialToken,
        ])->getJson('/api/v1/employees');

        // Assert response is successful
        $response->assertStatus(200);

        // Assert new token is returned in header
        $response->assertHeader('Authorization');
        $response->assertHeader('X-Token-Rotated', 'true');

        // Extract new token from header
        $authHeader = $response->headers->get('Authorization');
        $newToken = str_replace('Bearer ', '', $authHeader);

        // Assert new token is different from initial token
        $this->assertNotEquals($initialToken, $newToken);

        // Verify new token works for subsequent request
        $secondResponse = $this->withHeaders([
            'Authorization' => 'Bearer '.$newToken,
        ])->getJson('/api/v1/employees');

        $secondResponse->assertStatus(200);
    }

    /** @test */
    public function old_token_remains_valid_after_rotation()
    {
        // Login to get initial token
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $initialToken = $loginResponse->json('data.token');

        // Make authenticated request (this rotates the token)
        $this->withHeaders([
            'Authorization' => 'Bearer '.$initialToken,
        ])->getJson('/api/v1/employees');

        // The old token should still be valid (JWT tokens are stateless)
        // The security benefit is that the client uses the new rotated token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$initialToken,
        ])->getJson('/api/v1/employees');

        // This should still return 200 OK since JWT tokens don't get invalidated
        $response->assertStatus(200);
    }

    /** @test */
    public function unauthenticated_requests_do_not_rotate_tokens()
    {
        // Make unauthenticated request
        $response = $this->getJson('/api/v1/employees');

        // Should return 401
        $response->assertStatus(401);

        // Should not have token rotation headers
        $response->assertHeaderMissing('Authorization');
        $response->assertHeaderMissing('X-Token-Rotated');
    }
}
