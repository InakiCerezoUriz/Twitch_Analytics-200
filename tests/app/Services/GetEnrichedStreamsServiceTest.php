<?php

namespace Tests\App\Services;

use App\Infrastructure\TokenManager;
use App\Repositories\TwitchApiRepository;
use App\Services\GetEnrichedStreamsService;
use Illuminate\Http\JsonResponse;
use TwitchAnalytics\Tests\TestCase;

class GetEnrichedStreamsServiceTest extends TestCase
{
    private $twitchApiRepository;
    private $tokenManager;

    protected function setUp(): void
    {
        $this->twitchApiRepository = $this->createMock(TwitchApiRepository::class);
        $this->tokenManager        = $this->createMock(TokenManager::class);
    }

    /**
     * @test
     */
    public function givenInvalidTokenReturns401(): void
    {
        $this->tokenManager->method('getToken')->willReturn('not_valid_token');
        $this->twitchApiRepository->method('getEnrichedStreamsFromTwitchApi')
            ->with('not_valid_token', '3')
            ->willReturn([[], 401]);

        $service = new GetEnrichedStreamsService($this->twitchApiRepository, $this->tokenManager);

        $response = $service->getEnriched('3');

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals([
            'error' => 'Unauthorized. Twitch access token is invalid or has expired.',
        ], $response->getData(true));
    }

    /**
     * @test
     */
    public function whenTwitchApiFailsReturns500(): void
    {
        $this->tokenManager->method('getToken')->willReturn('valid_token');

        $this->twitchApiRepository
            ->method('getEnrichedStreamsFromTwitchApi')
            ->with('valid_token', '3')
            ->willReturn([[], 500]);

        $service = new GetEnrichedStreamsService($this->twitchApiRepository, $this->tokenManager);

        $response = $service->getEnriched('3');

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals([
            'error' => 'Internal Server error.',
        ], $response->getData(true));
    }

    /**
     * @test
     */
    public function givenValidTokenAndTwitchApiWorksReturns200(): void
    {
        $this->tokenManager->method('getToken')->willReturn('valid_token');

        $expectedData = [
            ['stream_id' => '1', 'title' => 'Stream_1'],
            ['stream_id' => '2', 'title' => 'Stream_2'],
            ['stream_id' => '3', 'title' => 'Stream_3'],
        ];

        $this->twitchApiRepository
            ->method('getEnrichedStreamsFromTwitchApi')
            ->with('valid_token', '3')
            ->willReturn([$expectedData, 200]);

        $service = new GetEnrichedStreamsService($this->twitchApiRepository, $this->tokenManager);

        $response = $service->getEnriched('3');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedData, $response->getData(true));
    }
}
