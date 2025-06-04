<?php

namespace TwitchAnalytics\Tests\Integration\Http\Controllers\GetUserById;

use TwitchAnalytics\Exceptions\EmptyOrInvalidIdException;
use TwitchAnalytics\Http\Controllers\GetUserById\GetUserByIdValidator;
use TwitchAnalytics\Infrastructure\TokenManager;
use TwitchAnalytics\Interfaces\DataBaseRepositoryInterface;
use TwitchAnalytics\Interfaces\TwitchApiRepositoryInterface;
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
        $mockValidator = \Mockery::mock(GetUserByIdValidator::class);
        $mockValidator->shouldReceive('validateId')
            ->once()
            ->with('invalid_id')
            ->andThrow(new EmptyOrInvalidIdException());

        $this->app->instance(GetUserByIdValidator::class, $mockValidator);

        $response = $this->call(
            'GET',
            '/analytics/user',
            ['id' => 'invalid_id'],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 8ac4f576cf1297671c3acd79bd7aa344'],
        );

        $response->assertStatus(400);
        $response->assertJson([
            'error' => "Invalid or missing 'id' parameter.",
        ]);
    }

    /**
     * @test
     */
    public function givenIdThatIsNotInTheDataBaseReturns200(): void
    {
        $mockResponse = [
            'id'                => 'id',
            'login'             => 'login',
            'display_name'      => 'display_name',
            'type'              => 'type',
            'broadcaster_type'  => 'broadcaster_type',
            'description'       => 'description',
            'profile_image_url' => 'profile_image_url',
            'offline_image_url' => 'offline_image_url',
            'view_count'        => 'view_count',
            'created_at'        => 'created_at',
        ];
        $mockResponse = ['data' => [$mockResponse]];


        $mockTokenManager = \Mockery::mock(TokenManager::class);
        $mockTokenManager->shouldReceive('getToken')
            ->once()
            ->andReturn('valid-token');

        $mockTokenManager->shouldReceive('tokenActive')
            ->once()
            ->andReturn(true);


        $mockUserValidator = \Mockery::mock(GetUserByIdValidator::class);
        $mockUserValidator->shouldReceive('validateId')
            ->once()
            ->with('valid_id')
            ->andReturn('valid_id');


        $mockDataBase = \Mockery::mock(DataBaseRepositoryInterface::class);
        $mockDataBase->shouldReceive('getUserFromDataBase')
            ->once()
            ->with('valid_id')
            ->andReturn(null);
        $mockDataBase->shouldReceive('insertUserInDataBase')
            ->once()
            ->with($mockResponse['data'][0])
            ->andReturn(null);


        $mockTwitchApiRepo = \Mockery::mock(TwitchApiRepositoryInterface::class);
        $mockTwitchApiRepo->shouldReceive('getUserFromTwitchApi')
            ->once()
            ->with('valid_id', 'valid-token')
            ->andReturn([json_encode($mockResponse),200]);


        $this->app->instance(TokenManager::class, $mockTokenManager);
        $this->app->instance(GetUserByIdValidator::class, $mockUserValidator);
        $this->app->instance(TwitchApiRepositoryInterface::class, $mockTwitchApiRepo);
        $this->app->instance(DataBaseRepositoryInterface::class, $mockDataBase);

        $response = $this->call(
            'GET',
            '/analytics/user',
            ['id' => 'valid_id'],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 8ac4f576cf1297671c3acd79bd7aa344']
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
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

    /**
     * @test
     */
    public function givenIdThatIsInTheDataBaseReturns200()
    {
        $mockResponse = [
            'id'                => 'id',
            'login'             => 'login',
            'display_name'      => 'display_name',
            'type'              => 'type',
            'broadcaster_type'  => 'broadcaster_type',
            'description'       => 'description',
            'profile_image_url' => 'profile_image_url',
            'offline_image_url' => 'offline_image_url',
            'view_count'        => 'view_count',
            'created_at'        => 'created_at',
        ];
        $mockResponse = ['data' => [$mockResponse]];


        $mockTokenManager = \Mockery::mock(TokenManager::class);
        $mockTokenManager->shouldReceive('getToken')
            ->once()
            ->andReturn('valid-token');

        $mockTokenManager->shouldReceive('tokenActive')
            ->once()
            ->andReturn(true);


        $mockUserValidator = \Mockery::mock(GetUserByIdValidator::class);
        $mockUserValidator->shouldReceive('validateId')
            ->once()
            ->with('valid_id')
            ->andReturn('valid_id');


        $mockDataBase = \Mockery::mock(DataBaseRepositoryInterface::class);
        $mockDataBase->shouldReceive('getUserFromDataBase')
            ->once()
            ->with('valid_id')
            ->andReturn($mockResponse['data'][0]);


        $this->app->instance(TokenManager::class, $mockTokenManager);
        $this->app->instance(GetUserByIdValidator::class, $mockUserValidator);
        $this->app->instance(DataBaseRepositoryInterface::class, $mockDataBase);

        $response = $this->call(
            'GET',
            '/analytics/user',
            ['id' => 'valid_id'],
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer 8ac4f576cf1297671c3acd79bd7aa344']
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
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
