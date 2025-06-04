<?php

namespace TwitchAnalytics\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Infrastructure\TokenManager;
use TwitchAnalytics\Interfaces\DataBaseRepositoryInterface;
use TwitchAnalytics\Interfaces\TwitchApiRepositoryInterface;
use TwitchAnalytics\Services\GetTopOfTopsService;

class GetTopOfTopsServiceTest extends TestCase
{
    private DataBaseRepositoryInterface $dataBaseRepository;
    private TwitchApiRepositoryInterface $twitchApiRepository;
    private TokenManager $tokenManager;
    private GetTopOfTopsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dataBaseRepository  = $this->createMock(DataBaseRepositoryInterface::class);
        $this->twitchApiRepository = $this->createMock(TwitchApiRepositoryInterface::class);
        $this->tokenManager        = $this->createMock(TokenManager::class);

        $this->service = new GetTopOfTopsService(
            $this->dataBaseRepository,
            $this->twitchApiRepository,
            $this->tokenManager
        );
    }

    /**
     * @test
     */
    public function returns404WhenTopGamesIsEmpty(): void
    {
        $this->tokenManager->method('getToken')->willReturn('valid_token');

        $this->twitchApiRepository->method('getTopGames')->willReturn([json_encode(['data' => []])]);

        $response = $this->service->getTopOfTops(null);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals(['error' => 'No top games data available.'], $response->getData(true));
    }

    /**
     * @test
     */
    public function returns200WithCachedTopStreamers(): void
    {
        $this->tokenManager->method('getToken')->willReturn('valid_token');

        $topGames = [['id' => '1', 'name' => 'Game 1']];
        $this->twitchApiRepository->method('getTopGames')->willReturn([json_encode(['data' => $topGames])]);

        $this->dataBaseRepository->method('getUltimaSolicitud')->willReturn(time());
        $this->dataBaseRepository->method('getTopStreamer')->willReturn([
            ['name' => 'TopStreamer', 'game' => 'Game 1'],
        ]);

        $response = $this->service->getTopOfTops('600');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([['name' => 'TopStreamer', 'game' => 'Game 1']], $response->getData(true));
    }
}
