<?php

namespace TwitchAnalytics\Tests\Http\Controllers\Register;

use Illuminate\Http\JsonResponse;
use TwitchAnalytics\Services\UserRegisterService;
use TwitchAnalytics\Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    /**
     * @test
     */
    public function gets400WhenEmailParameterIsMissing(): void
    {
        $response = $this->call(
            'POST',
            '/register'
        );

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'The email is mandatory',
        ]);
    }

    /**
     * @test
     */
    public function gets400WhenEmailParameterIsInvalid(): void
    {
        $response = $this->call(
            'POST',
            '/register',
            [
                'email' => 'invalid-email',
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
    public function gets200WhenEmailParameterValid(): void
    {
        $mockResponse = new JsonResponse([
            'apiKey' => 'api_key_value',
        ], 200);

        $mockService = \Mockery::mock(UserRegisterService::class);
        $mockService->shouldReceive('register')
            ->once()
            ->with('test@example.com')
            ->andReturn($mockResponse);

        $this->app->instance(UserRegisterService::class, $mockService);

        $response = $this->call(
            'POST',
            '/register',
            [
                'email' => 'test@example.com',
            ]
        );

        $response->assertStatus(200);
        $response->assertJson([
            'apiKey' => 'api_key_value',
        ]);
    }
}
