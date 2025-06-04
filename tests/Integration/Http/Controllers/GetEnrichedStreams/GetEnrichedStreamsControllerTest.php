<?php

namespace TwitchAnalytics\Tests\Integration\Http\Controllers\GetEnrichedStreams;

use TwitchAnalytics\Infrastructure\TokenManager;
use TwitchAnalytics\Interfaces\TwitchApiRepositoryInterface;
use TwitchAnalytics\Tests\TestCase;

class GetEnrichedStreamsControllerTest extends TestCase
{
    /**
     * @test
     */
    public function givenMissingLimitReturns400(): void
    {
        $response = $this->call(
            'GET',
            '/analytics/streams/enriched',
            [],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 8ac4f576cf1297671c3acd79bd7aa344']
        );

        $response->assertStatus(400);
        $response->assertJson([
            'error' => "Invalid 'limit' parameter.",
        ]);
    }

    /**
     * @test
     */
    public function givenInvalidLimitReturns400(): void
    {
        $response = $this->call(
            'GET',
            '/analytics/streams/enriched',
            ['limit' => 'invalid_limit'],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 8ac4f576cf1297671c3acd79bd7aa344']
        );

        $response->assertStatus(400);
        $response->assertJson([
            'error' => "Invalid 'limit' parameter.",
        ]);
    }

    /**
     * @test
     */
    public function givenInvalidTokenReturns401(): void
    {
        $mockTokenManager = \Mockery::mock(TokenManager::class);
        $mockTokenManager->shouldReceive('getToken')
            ->once()
            ->andReturn('invalid-token');

        $mockTokenManager->shouldReceive('tokenActive')
            ->once()
            ->andReturn(true);

        $mockTwitchApiRepo = \Mockery::mock(TwitchApiRepositoryInterface::class);
        $mockTwitchApiRepo->shouldReceive('getEnrichedStreamsFromTwitchApi')
            ->once()
            ->with('invalid-token', '1')
            ->andReturn([[], 401]);

        $this->app->instance(TokenManager::class, $mockTokenManager);
        $this->app->instance(TwitchApiRepositoryInterface::class, $mockTwitchApiRepo);

        $response = $this->call(
            'GET',
            '/analytics/streams/enriched',
            ['limit' => '1'],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 8ac4f576cf1297671c3acd79bd7aa344']
        );

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'Unauthorized. Twitch access token is invalid or has expired.',
        ]);
    }

    /**
     * @test
     */
    public function givenValidLimitReturns200(): void
    {
        $mockData = [
            [
                'stream_id'         => '1',
                'user_id'           => '1',
                'user_name'         => 'user_name',
                'viewer_count'      => '100',
                'title'             => 'title',
                'user_display_name' => 'user_display_name',
                'profile_image_url' => 'profile_image_url',
            ],
        ];

        $mockTokenManager = \Mockery::mock(TokenManager::class);
        $mockTokenManager->shouldReceive('getToken')
            ->once()
            ->andReturn('valid-token');

        $mockTokenManager->shouldReceive('tokenActive')
            ->once()
            ->andReturn(true);

        $mockTwitchApiRepo = \Mockery::mock(TwitchApiRepositoryInterface::class);
        $mockTwitchApiRepo->shouldReceive('getEnrichedStreamsFromTwitchApi')
            ->once()
            ->with('valid-token', '1')
            ->andReturn([$mockData, 200]);

        $this->app->instance(TokenManager::class, $mockTokenManager);
        $this->app->instance(TwitchApiRepositoryInterface::class, $mockTwitchApiRepo);

        $response = $this->call(
            'GET',
            '/analytics/streams/enriched',
            ['limit' => '1'],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 8ac4f576cf1297671c3acd79bd7aa344']
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'stream_id',
                'user_id',
                'user_name',
                'viewer_count',
                'title',
                'user_display_name',
                'profile_image_url',
            ],
        ]);
    }
}
