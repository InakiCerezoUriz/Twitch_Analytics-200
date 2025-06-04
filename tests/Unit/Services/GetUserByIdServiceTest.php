<?php

namespace TwitchAnalytics\Tests\Unit\Services;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Infrastructure\TokenManager;
use TwitchAnalytics\Interfaces\DataBaseRepositoryInterface;
use TwitchAnalytics\Interfaces\TwitchApiRepositoryInterface;
use TwitchAnalytics\Services\GetUserByIdService;

class GetUserByIdServiceTest extends TestCase
{
    private DataBaseRepositoryInterface $dataBaseRepository;
    private TwitchApiRepositoryInterface $twitchApiRepository;
    private TokenManager $tokenManager;

    protected GetUserByIdService $service;



    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->dataBaseRepository  = $this->createMock(DataBaseRepositoryInterface::class);
        $this->twitchApiRepository = $this->createMock(TwitchApiRepositoryInterface::class);
        $this->tokenManager        = $this->createMock(TokenManager::class);

        $this->service = new GetUserByIdService(
            $this->dataBaseRepository,
            $this->twitchApiRepository,
            $this->tokenManager
        );
    }

    /**
     * @test
     */
    public function receivingEmptyUserDataFromTwitchReturns404(): void
    {
        $this->dataBaseRepository->method('getUserFromDataBase')
            ->willReturn(null);

        $this->tokenManager->method('getToken')
            ->willReturn('valid_token');

        $this->twitchApiRepository->method('getUserFromTwitchApi')
            ->willReturn([json_encode(['data' => []]), 200]);

        $response = $this->service->getUser('123');

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals(['error' => 'User not found.'], $response->getData(true));
    }

    /**
     * @test
     */
    public function givenInvalidIdReturns400(): void
    {
        $this->dataBaseRepository->method('getUserFromDataBase')
            ->willReturn(null);

        $this->tokenManager->method('getToken')
            ->willReturn('valid_token');

        $this->twitchApiRepository->method('getUserFromTwitchApi')
            ->willReturn(['{}', 400]);

        $response = $this->service->getUser('invalid');

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals(['error' => "Invalid or missing 'id' parameter."], $response->getData(true));
    }

    /**
     * @test
     */
    public function givenInvalidTokenReturns401(): void
    {
        $this->dataBaseRepository->method('getUserFromDataBase')
            ->willReturn(null);

        $this->tokenManager->method('getToken')
            ->willReturn('invalid_token');

        $this->twitchApiRepository->method('getUserFromTwitchApi')
            ->willReturn(['{}', 401]);

        $response = $this->service->getUser('123');

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals(['error' => 'Unauthorized. Twitch access token is invalid or has expired.'], $response->getData(true));
    }

    /**
     * @test
     */
    public function whenTwitchApiFailsReturns500(): void
    {
        $this->dataBaseRepository->method('getUserFromDataBase')
            ->willReturn(null);

        $this->tokenManager->method('getToken')
            ->willReturn('valid_token');

        $this->twitchApiRepository->method('getUserFromTwitchApi')
            ->willReturn(['{}', 500]);

        $response = $this->service->getUser('123');

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals(['error' => 'Internal Server error.'], $response->getData(true));
    }

    /**
     * @test
     */
    public function givenValidIdReturnsUserAndInsertsIntoDb(): void
    {
        $this->dataBaseRepository->method('getUserFromDataBase')
            ->willReturn(null);

        $this->tokenManager->method('getToken')
            ->willReturn('valid_token');

        $userData = ['id' => '123', 'name' => 'test_user'];

        $this->twitchApiRepository->method('getUserFromTwitchApi')
            ->willReturn([json_encode(['data' => [$userData]]), 200]);

        $this->dataBaseRepository
            ->expects($this->once())
            ->method('insertUserInDataBase')
            ->with($userData);

        $response = $this->service->getUser('123');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($userData, $response->getData(true));
    }
}
