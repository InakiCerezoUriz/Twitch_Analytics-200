<?php

namespace TwitchAnalytics\Tests\app\Services;

use App\Infrastructure\TokenManager;
use App\Interfaces\DataBaseRepositoryInterface;
use App\Interfaces\TwitchApiRepositoryInterface;
use App\Models\TopStreamer;
use App\Services\GetTopOfTopsService;
use Illuminate\Http\JsonResponse;
use PHPUnit\Framework\TestCase;

class GetTopOfTopsServiceTest extends TestCase
{
    private DataBaseRepositoryInterface $dataBaseRepository;
    private TwitchApiRepositoryInterface $twitchApiRepository;
    private TokenManager $tokenManager;
    private GetTopOfTopsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dataBaseRepository = $this->createMock(DataBaseRepositoryInterface::class);
        $this->twitchApiRepository = $this->createMock(TwitchApiRepositoryInterface::class);
        $this->tokenManager = $this->createMock(TokenManager::class);

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
            ['name' => 'TopStreamer', 'game' => 'Game 1']
        ]);

        $response = $this->service->getTopOfTops('600');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([['name' => 'TopStreamer', 'game' => 'Game 1']], $response->getData(true));
    }
}
