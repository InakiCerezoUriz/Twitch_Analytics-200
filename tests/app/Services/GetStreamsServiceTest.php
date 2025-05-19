<?php

namespace Tests\App\Services;

use App\Infrastructure\TokenManager;
use App\Models\Stream;
use App\Repositories\TwitchApiRepository;
use App\Services\GetStreamsService;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Testing\TestCase;
use PHPUnit\Framework\MockObject\Exception;

class GetStreamsServiceTest extends TestCase
{
    private $twitchApiRepository;
    private $tokenManager;
    private GetStreamsService $service;

    public function createApplication()
    {
        return require __DIR__ . '/../../../bootstrap/app.php';
    }
    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->twitchApiRepository = $this->createMock(TwitchApiRepository::class);
        $this->tokenManager = $this->createMock(TokenManager::class);

        $this->app->instance(TwitchApiRepository::class, $this->twitchApiRepository);
        $this->app->instance(TokenManager::class, $this->tokenManager);

        $this->service = $this->app->make(GetStreamsService::class);
    }

    /**
     * @test
     */
    public function givenValidTokenReturnsStreamList(): void
    {
        $this->tokenManager->method('getToken')->willReturn('valid_token');

        $mockApiResponse = [
            new Stream('Stream 1', 'User1'),
            new Stream('Stream 2', 'User2'),
            new Stream('Stream 3', 'User3')
        ];

        $this->twitchApiRepository->method('getStreamsFromTwitchApi')
            ->with('valid_token')
            ->willReturn([$mockApiResponse, 200]);

        $response = $this->service->getStreams();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([
            ['title' => 'Stream 1', 'user_name' => 'User1'],
            ['title' => 'Stream 2', 'user_name' => 'User2'],
            ['title' => 'Stream 3', 'user_name' => 'User3'],
        ], $response->getData(true));
    }

    /**
     * @test
     */
    public function givenInvalidTokenReturns401(): void
    {
        $this->tokenManager->method('getToken')->willReturn('not_valid_token');

        $this->twitchApiRepository->method('getStreamsFromTwitchApi')
            ->with('not_valid_token')
            ->willReturn(['{}', 401]);

        $response = $this->service->getStreams();

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals([
            'error' => 'Unauthorized. Twitch access token is invalid or has expired.',
        ], $response->getData(true));
    }

    /**
     * @test
     */
    public function givenUnexpectedErrorReturns500(): void
    {
        $this->tokenManager->method('getToken')->willReturn('valid_token');

        $this->twitchApiRepository->method('getStreamsFromTwitchApi')
            ->with('valid_token')
            ->willReturn(['{}', 500]);

        $response = $this->service->getStreams();

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals([
            'error' => 'Internal Server error.',
        ], $response->getData(true));
    }
}
