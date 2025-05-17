<?php

namespace TwitchAnalytics\Tests\app\Http\Controllers\Token;

use App\Services\TokenService;
use Illuminate\Http\JsonResponse;
use Mockery;
use TwitchAnalytics\Tests\TestCase;

class TokenControllerTest extends TestCase
{
    /**
     * @test
     */
    public function gets400WhenEmailParameterIsInvalid(): void
    {
        $response = $this->call(
            'POST',
            '/token',
            [
                'email'   => 'invalid-email',
                'api_key' => 'valid-api-key',
            ]
        );

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'The email must be a valid email address',
        ]);
    }

    /**
     * @test
     */
    public function gets400WhenEmailParameterIsMissing(): void
    {
        $response = $this->call(
            'POST',
            '/token',
            [
                'api_key' => 'valid-api-key',
            ]
        );

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'The email is mandatory',
        ]);
    }

    /**
     * @test
     */
    public function gets400WhenApiKeyParameterIsMissing(): void
    {
        $response = $this->call(
            'POST',
            '/token',
            [
                'email' => 'test@example.com',
            ]
        );

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'The api_key is mandatory',
        ]);
    }

    /**
     * @test
     */
    public function gets200WhenEmailAndApiKeyParametersAreValid(): void
    {
        $token        = 'valid-token';
        $mockResponse = new JsonResponse([
            'token' => $token,
        ], 200);

        $mockService = Mockery::mock(TokenService::class);
        $mockService->shouldReceive('getToken')
            ->once()
            ->with('test@example.com', 'valid-api-key')
            ->andReturn($mockResponse);

        $this->app->instance(TokenService::class, $mockService);

        $response = $this->call(
            'POST',
            '/token',
            [
                'email'   => 'test@example.com',
                'api_key' => 'valid-api-key',
            ]
        );

        $response->assertStatus(200);
        $response->assertJson([
            'token' => $token,
        ]);
    }
}
