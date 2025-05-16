<?php

namespace TwitchAnalytics\Tests\app\Http\Controllers\GetStreams;

use App\Services\GetStreamsService;
use Illuminate\Http\JsonResponse;
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
    public function calledGetStreamsReturns200(): void
    {
        $mockResponse = new JsonResponse([
            'title',
            'user_name',
        ], 200);

        $mockService = \Mockery::mock(GetStreamsService::class);
        $mockService->shouldReceive('getStreams')
            ->once()
            ->andReturn($mockResponse);

        $this->app->instance(GetStreamsService::class, $mockService);

        $response = $this->call(
            'GET',
            '/analytics/streams',
            [],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 8ac4f576cf1297671c3acd79bd7aa344']
        );
        $response->assertStatus(200);
        $response->assertJson([
            'title',
            'user_name'
        ]);
    }
}
