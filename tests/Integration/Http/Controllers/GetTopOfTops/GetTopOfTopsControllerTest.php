<?php

namespace TwitchAnalytics\Tests\Integration\Http\Controllers\GetTopOfTops;

use TwitchAnalytics\Infrastructure\TokenManager;
use TwitchAnalytics\Interfaces\DataBaseRepositoryInterface;
use TwitchAnalytics\Interfaces\TwitchApiRepositoryInterface;
use TwitchAnalytics\Models\TopStreamer;
use TwitchAnalytics\Tests\TestCase;

class GetTopOfTopsControllerTest extends TestCase
{
    /**
     * @test
     */
    public function givenInvalidSinceReturns400(): void
    {
        $response = $this->call(
            'GET',
            '/analytics/topsofthetops',
            ['since' => 'invalid_since'],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 8ac4f576cf1297671c3acd79bd7aa344'],
        );

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Bad Request. Invalid or missing parameters.',
        ]);
    }

    /**
     * @test
     */
    public function givenSinceReturns200(): void
    {
        // Datos simulados que devuelve getTopStreamer (una fila del resultado final)
        $mockTopStreamerData = [
            'game_id'                => '123',
            'game_name'              => 'FIFA 25',
            'user_name'              => 'streamer1',
            'total_videos'           => 10,
            'total_views'            => 10000,
            'most_viewed_title'      => 'Final épica',
            'most_viewed_views'      => 5000,
            'most_viewed_duration'   => '02:00:00',
            'most_viewed_created_at' => '2025-06-01T12:00:00Z',
        ];

        $mockTopGameData = [
            [
                'id'          => '509658',
                'name'        => 'Just Chatting',
                'box_art_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/509658-{width}x{height}.jpg',
                'igdb_id'     => '',
            ],
        ];

        $mockGame = [
            'id'          => '509658',
            'name'        => 'Just Chatting',
            'box_art_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/509658-{width}x{height}.jpg',
            'igdb_id'     => '',
        ];

        $mockStreams = [
            'user_name'  => 'streamer1',
            'title'      => 'Final épica',
            'view_count' => 5000,
            'duration'   => '02:00:00',
            'created_at' => '2025-06-01T12:00:00Z',
        ];

        $topStreamerStreams = 10;
        $totalViews         = 10000;

        $mockTopStreamerObject = new TopStreamer($mockGame, $mockStreams, $topStreamerStreams, $totalViews);


        $mockTokenManager = \Mockery::mock(TokenManager::class);
        $mockTokenManager->shouldReceive('getToken')
            ->once()
            ->andReturn('valid-token');

        $mockTokenManager->shouldReceive('tokenActive')
            ->once()
            ->andReturn(true);

        $mockTwitchApiRepo = \Mockery::mock(TwitchApiRepositoryInterface::class);
        $mockTwitchApiRepo->shouldReceive('getTopGames')
            ->once()
            ->with('valid-token')
            ->andReturn([json_encode(['data' => $mockTopGameData]), 200]);

        $mockTwitchApiRepo->shouldReceive('getTopStreamer')
            ->once()
            ->with($mockTopGameData[0], 'valid-token')
            ->andReturn($mockTopStreamerObject);

        $mockDbRepo = \Mockery::mock(DataBaseRepositoryInterface::class);
        $mockDbRepo->shouldReceive('getUltimaSolicitud')
            ->once()
            ->andReturn(time() - 1000);

        $mockDbRepo->shouldReceive('clearCache')
            ->once();

        $mockDbRepo->shouldReceive('insertarTopStreamer')
            ->once()
            ->with($mockTopStreamerObject);

        $mockDbRepo->shouldReceive('getTopStreamer')
            ->once()
            ->andReturn([$mockTopStreamerData]);

        $this->app->instance(TokenManager::class, $mockTokenManager);
        $this->app->instance(TwitchApiRepositoryInterface::class, $mockTwitchApiRepo);
        $this->app->instance(DataBaseRepositoryInterface::class, $mockDbRepo);

        $response = $this->call(
            'GET',
            '/analytics/topsofthetops',
            ['since' => '8'],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 8ac4f576cf1297671c3acd79bd7aa344'],
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            [
                'game_id',
                'game_name',
                'user_name',
                'total_videos',
                'total_views',
                'most_viewed_title',
                'most_viewed_views',
                'most_viewed_duration',
                'most_viewed_created_at',
            ],
        ]);
    }
}
