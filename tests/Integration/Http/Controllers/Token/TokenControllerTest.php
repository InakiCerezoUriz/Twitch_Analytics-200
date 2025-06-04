<?php

namespace TwitchAnalytics\Tests\Integration\Http\Controllers\Token;
use TwitchAnalytics\Interfaces\DataBaseRepositoryInterface;
use TwitchAnalytics\Infrastructure\TokenManager;
use TwitchAnalytics\Tests\TestCase;

class TokenControllerTest extends TestCase
{
    /**
     * @test
     */
    public function gets400WhenEmailParameterIsInvalid(): void
    {
        $response = $this->call(
            'POST',
            '/token',
            [
                'email'   => 'invalid-email',
                'api_key' => 'valid-api-key',
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
    public function gets400WhenEmailParameterIsMissing(): void
    {
        $response = $this->call(
            'POST',
            '/token',
            [
                'api_key' => 'valid-api-key',
            ]
        );

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'The email is mandatory',
        ]);
    }

    /**
     * @test
     */
    public function gets400WhenApiKeyParameterIsMissing(): void
    {
        $response = $this->call(
            'POST',
            '/token',
            [
                'email' => 'test@example.com',
            ]
        );

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'The api_key is mandatory',
        ]);
    }

    /**
     * @test
     */
    public function gets401WhenUserDoesNotExist(): void
    {
        $mockDbRepo = \Mockery::mock(DataBaseRepositoryInterface::class);
        $mockDbRepo->shouldReceive('getTokenFromDataBase')
            ->once()
            ->with('notfound@example.com')
            ->andReturn(null);

        $this->app->instance(DataBaseRepositoryInterface::class, $mockDbRepo);

        $response = $this->call(
            'POST',
            '/token',
            [
                'email'   => 'notfound@example.com',
                'api_key' => 'any-api-key',
            ]
        );

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'Unauthorized. API access token is invalid.',
        ]);
    }

    /**
     * @test
     */
    public function gets200WhenEmailAndApiKeyParametersAreValid(): void
    {
        $mockDbRepo = \Mockery::mock(DataBaseRepositoryInterface::class);
        $mockDbRepo->shouldReceive('getTokenFromDataBase')
            ->once()
            ->with('test@example.com')
            ->andReturn([
                'email'            => 'test@example.com',
                'api_key'          => 'valid-api-key',
                'token'            => null,
                'fechaexpiracion'  => '2099-12-31 23:59:59',
            ]);

        $mockDbRepo->shouldReceive('updateUserTokenInDataBase')
            ->once()
            ->with(['token' => 'generated-token'], 'test@example.com');

        $mockTokenManager = \Mockery::mock(TokenManager::class);
        $mockTokenManager->shouldReceive('generarToken')
            ->once()
            ->andReturn(['token' => 'generated-token']);

        $this->app->instance(DataBaseRepositoryInterface::class, $mockDbRepo);
        $this->app->instance(TokenManager::class, $mockTokenManager);

        $response = $this->call(
            'POST',
            '/token',
            [
                'email'   => 'test@example.com',
                'api_key' => 'valid-api-key',
            ]
        );

        $response->assertStatus(200);
        $response->assertJson([
            'token' => 'generated-token',
        ]);
    }

    /**
     * @test
     */
    public function gets200WithExistingValidToken(): void
    {
        $mockDbRepo = \Mockery::mock(DataBaseRepositoryInterface::class);
        $mockDbRepo->shouldReceive('getTokenFromDataBase')
            ->once()
            ->with('test@example.com')
            ->andReturn([
                'email'            => 'test@example.com',
                'api_key'          => 'valid-api-key',
                'token'            => 'existing-token',
                'fechaexpiracion'  => '2099-12-31 23:59:59',
            ]);

        $mockTokenManager = \Mockery::mock(TokenManager::class);
        $mockTokenManager->shouldNotReceive('generarToken');

        $mockDbRepo->shouldNotReceive('updateUserTokenInDataBase');

        $this->app->instance(DataBaseRepositoryInterface::class, $mockDbRepo);
        $this->app->instance(TokenManager::class, $mockTokenManager);

        $response = $this->call(
            'POST',
            '/token',
            [
                'email'   => 'test@example.com',
                'api_key' => 'valid-api-key',
            ]
        );

        $response->assertStatus(200);
        $response->assertJson([
            'token' => 'existing-token',
        ]);
    }

}
