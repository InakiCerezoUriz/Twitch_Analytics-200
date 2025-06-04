<?php

namespace TwitchAnalytics\Tests\Integration\Http\Controllers\GetStreams;

use TwitchAnalytics\Infrastructure\TokenManager;
use TwitchAnalytics\Interfaces\TwitchApiRepositoryInterface;
use TwitchAnalytics\Tests\TestCase;

class GetStreamsControllerTest extends TestCase
{
    /**
     * @test
     */
    public function givenInvalidTokenReturns401(): void
    {
        $response = $this->call(
            'GET',
            '/analytics/streams',
            [],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 8ac4f576cf1297671c3acd79bd7aa34'] // Falta un 4 al final
        );

        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function givenInvalidTwitchTokenReturns401(): void
    {
        $mockTokenManager = \Mockery::mock(TokenManager::class);
        $mockTokenManager->shouldReceive('getToken')
            ->once()
            ->andReturn('invalid-token');

        $mockTokenManager->shouldReceive('tokenActive')
            ->once()
            ->andReturn(true);

        $mockTwitchApiRepo = \Mockery::mock(TwitchApiRepositoryInterface::class);
        $mockTwitchApiRepo->shouldReceive('getStreamsFromTwitchApi')
            ->once()
            ->with('invalid-token')
            ->andReturn([[], 401]);

        $this->app->instance(TokenManager::class, $mockTokenManager);
        $this->app->instance(TwitchApiRepositoryInterface::class, $mockTwitchApiRepo);

        $response = $this->call(
            'GET',
            '/analytics/streams',
            [],
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
    public function calledGetStreamsReturns200(): void
    {
        $streamMock = \Mockery::mock();
        $streamMock->shouldReceive('getStream')
            ->once()
            ->andReturn([
                'title'     => 'title',
                'user_name' => 'user_name',
            ]);

        $mockResponse = [ $streamMock ];

        $mockTokenManager = \Mockery::mock(TokenManager::class);
        $mockTokenManager->shouldReceive('getToken')
            ->once()
            ->andReturn('valid-token');

        $mockTokenManager->shouldReceive('tokenActive')
            ->once()
            ->andReturn(true);

        $mockTwitchApiRepo = \Mockery::mock(TwitchApiRepositoryInterface::class);
        $mockTwitchApiRepo->shouldReceive('getStreamsFromTwitchApi')
            ->once()
            ->with('valid-token')
            ->andReturn([$mockResponse, 200]);

        $this->app->instance(TokenManager::class, $mockTokenManager);
        $this->app->instance(TwitchApiRepositoryInterface::class, $mockTwitchApiRepo);

        $response = $this->call(
            'GET',
            '/analytics/streams',
            [],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 8ac4f576cf1297671c3acd79bd7aa344']
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'title',
                'user_name',
            ],
        ]);
    }
}
