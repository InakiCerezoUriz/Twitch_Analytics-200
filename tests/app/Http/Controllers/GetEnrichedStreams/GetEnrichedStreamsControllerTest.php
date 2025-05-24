<?php

namespace GetEnrichedStreams;

use App\Services\GetEnrichedStreamsService;
use Illuminate\Http\JsonResponse;
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
            ],[
                'stream_id'         => '1',
                'user_id'           => '1',
                'user_name'         => 'user_name',
                'viewer_count'      => '100',
                'title'             => 'title',
                'user_display_name' => 'user_display_name',
                'profile_image_url' => 'profile_image_url',
            ],
        ];

        $mockResponse = new JsonResponse($mockData, 200);


        $mockService = \Mockery::mock(GetEnrichedStreamsService::class);
        $mockService->shouldReceive('getEnriched')
            ->once()
            ->with('1')
            ->andReturn($mockResponse);

        $this->app->instance(GetEnrichedStreamsService::class, $mockService);

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
