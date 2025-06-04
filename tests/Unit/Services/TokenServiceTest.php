<?php

namespace TwitchAnalytics\Tests\Unit\Services;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Infrastructure\TokenManager;
use TwitchAnalytics\Interfaces\DataBaseRepositoryInterface;
use TwitchAnalytics\Services\TokenService;

class TokenServiceTest extends TestCase
{
    private DataBaseRepositoryInterface $dataBaseRepository;
    private TokenManager $tokenManager;
    private TokenService $service;


    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->dataBaseRepository = $this->createMock(DataBaseRepositoryInterface::class);
        $this->tokenManager       = $this->createMock(TokenManager::class);

        $this->service = new TokenService(
            $this->dataBaseRepository,
            $this->tokenManager
        );
    }

    /**
     * @test
     */
    public function givenNonexistentUserReturns401(): void
    {
        $this->dataBaseRepository->method('getTokenFromDataBase')->willReturn(null);

        $this->response = $this->service->getToken('example@mail.com', 'api_key');

        $this->assertEquals(401, $this->response->getStatusCode());
        $this->assertEquals([
            'error' => 'Unauthorized. API access token is invalid.',
        ], $this->response->getData(true));
    }

    /**
     * @test
     */
    public function givenInvalidApiKeyOrEmailReturns401(): void
    {
        $this->dataBaseRepository->method('getTokenFromDataBase')->willReturn([
            'email'           => 'example@mail.com',
            'api_key'         => 'correct_key',
            'token'           => 'abc123',
            'fechaexpiracion' => date('Y-m-d H:i:s', strtotime('+1 hour')),
        ]);

        $this->response = $this->service->getToken('example@mail.com', 'not_valid_key');

        $this->assertEquals(401, $this->response->getStatusCode());
        $this->assertEquals([
            'error' => 'Unauthorized. API access token is invalid.',
        ], $this->response->getData(true));
    }

    /**
     * @test
     */
    public function givenExpiredTokenGeneratesNewToken(): void
    {
        $expiredDate = date('Y-m-d H:i:s', strtotime('-1 hour'));

        $this->dataBaseRepository->method('getTokenFromDataBase')->willReturn([
            'email'           => 'example@mail.com',
            'api_key'         => 'valid_key',
            'token'           => null,
            'fechaexpiracion' => $expiredDate,
        ]);

        $newToken = ['token' => 'new_generated_token'];

        $this->tokenManager->method('generarToken')->willReturn($newToken);
        $this->dataBaseRepository->expects($this->once())
            ->method('updateUserTokenInDataBase')
            ->with($newToken, 'example@mail.com');

        $this->response = $this->service->getToken('example@mail.com', 'valid_key');

        $this->assertEquals(200, $this->response->getStatusCode());
        $this->assertEquals(['token' => 'new_generated_token'], $this->response->getData(true));
    }

    /**
     * @test
     */
    public function givenValidTokenReturnsToken(): void
    {
        $futureDate = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->dataBaseRepository->method('getTokenFromDataBase')->willReturn([
            'email'           => 'example@mail.com',
            'api_key'         => 'valid_key',
            'token'           => 'existing_token',
            'fechaexpiracion' => $futureDate,
        ]);

        $this->response = $this->service->getToken('example@mail.com', 'valid_key');

        $this->assertEquals(200, $this->response->getStatusCode());
        $this->assertEquals(['token' => 'existing_token'], $this->response->getData(true));
    }
}
