<?php

namespace TwitchAnalytics\Tests\app\Http\Controllers\GetUserById;

use App\Services\GetUserByIdService;
use Illuminate\Http\JsonResponse;
use TwitchAnalytics\Tests\TestCase;

class GetUserByIdControllerTest extends TestCase
{
    /**
     * @test
     */
    public function givenMissingIdReturns400(): void
    {
        $response = $this->call(
            'GET',
            '/analytics/user',
            [],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 8ac4f576cf1297671c3acd79bd7aa344']
        );

        $response->assertStatus(400);
        $response->assertJson([
            'error' => "Invalid or missing 'id' parameter.",
        ]);
    }

    /**
     * @test
     */
    public function givenInvalidIdReturns400(): void
    {
        $response = $this->call(
            'GET',
            '/analytics/user',
            [],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 8ac4f576cf1297671c3acd79bd7aa344'],
            ['id'                 => 'invalid_id']
        );

        $response->assertStatus(400);
        $response->assertJson([
            'error' => "Invalid or missing 'id' parameter.",
        ]);
    }

    /**
     * @test
     */
    public function givenIdReturns200(): void
    {
        $mockResponse = new JsonResponse([
            'id',
            'login',
            'display_name',
            'type',
            'broadcaster_type',
            'description',
            'profile_image_url',
            'offline_image_url',
            'view_count',
            'created_at',
        ], 200);

        $mockService = \Mockery::mock(GetUserByIdService::class);
        $mockService->shouldReceive('getUser')
            ->once()
            ->with('1')
            ->andReturn($mockResponse);

        $this->app->instance(GetUserByIdService::class, $mockService);

        $response = $this->call(
            'GET',
            '/analytics/user',
            ['id' => '1'],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 8ac4f576cf1297671c3acd79bd7aa344']
        );

        $response->assertStatus(200);
        $response->assertJson([
            'id',
            'login',
            'display_name',
            'type',
            'broadcaster_type',
            'description',
            'profile_image_url',
            'offline_image_url',
            'view_count',
            'created_at',
        ]);
    }
}
