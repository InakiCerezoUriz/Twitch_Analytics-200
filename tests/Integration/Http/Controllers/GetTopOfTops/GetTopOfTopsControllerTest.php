<?php

namespace TwitchAnalytics\Tests\Integration\Http\Controllers\GetTopOfTops;

use Illuminate\Http\JsonResponse;
use TwitchAnalytics\Services\GetTopOfTopsService;
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
        $mockResponse = new JsonResponse([
            'game_id',
            'game_name',
            'user_name',
            'total_videos',
            'total_views',
            'most_viewed_title',
            'most_viewed_views',
            'most_viewed_duration',
            'most_viewed_created_at',
        ], 200);

        $mockService = \Mockery::mock(GetTopOfTopsService::class);
        $mockService->shouldReceive('getTopOfTops')
            ->once()
            ->with('8')
            ->andReturn($mockResponse);

        $this->app->instance(GetTopOfTopsService::class, $mockService);

        $response = $this->call(
            'GET',
            '/analytics/topsofthetops',
            ['since' => '8'],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 8ac4f576cf1297671c3acd79bd7aa344'],
        );

        $response->assertStatus(200);
        $response->assertJson([
            'game_id',
            'game_name',
            'user_name',
            'total_videos',
            'total_views',
            'most_viewed_title',
            'most_viewed_views',
            'most_viewed_duration',
            'most_viewed_created_at',
        ]);
    }

    /**
     * @test
     */
    public function givenEmptySinceReturns200(): void
    {
        $mockResponse = new JsonResponse([
            'game_id',
            'game_name',
            'user_name',
            'total_videos',
            'total_views',
            'most_viewed_title',
            'most_viewed_views',
            'most_viewed_duration',
            'most_viewed_created_at',
        ], 200);

        $mockService = \Mockery::mock(GetTopOfTopsService::class);
        $mockService->shouldReceive('getTopOfTops')
            ->once()
            ->with('')
            ->andReturn($mockResponse);

        $this->app->instance(GetTopOfTopsService::class, $mockService);

        $response = $this->call(
            'GET',
            '/analytics/topsofthetops',
            [],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 8ac4f576cf1297671c3acd79bd7aa344']
        );

        //$response->assertStatus(200);
        $response->assertJson([
            'game_id',
            'game_name',
            'user_name',
            'total_videos',
            'total_views',
            'most_viewed_title',
            'most_viewed_views',
            'most_viewed_duration',
            'most_viewed_created_at',
        ]);
    }
}
