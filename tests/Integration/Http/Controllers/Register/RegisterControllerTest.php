<?php

namespace TwitchAnalytics\Tests\Integration\Http\Controllers\Register;

use TwitchAnalytics\Interfaces\DataBaseRepositoryInterface;
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
        $mockData = [
            [
                'api_key' => 'api_key_value',
            ],
        ];

        $mockDataBaseRepo = \Mockery::mock(DataBaseRepositoryInterface::class);
        $mockDataBaseRepo->shouldReceive('getApiKey')
            ->once()
            ->andReturn('api_key_value');

        $mockDataBaseRepo->shouldReceive('updateApiKey')
            ->once()
            ->andReturn([$mockData, 200]);

        $this->app->instance(DataBaseRepositoryInterface::class, $mockDataBaseRepo);

        $response = $this->call(
            'POST',
            '/register',
            [
                'email' => 'test@example.com',
            ]
        );

        $response->assertStatus(200);
        $response->assertJson([
            'api_key' => 'api_key_value',
        ]);
    }
}
