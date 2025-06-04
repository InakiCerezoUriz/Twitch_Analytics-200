<?php

namespace TwitchAnalytics\Services;

use Illuminate\Http\JsonResponse;
use TwitchAnalytics\Infrastructure\TokenManager;
use TwitchAnalytics\Interfaces\DataBaseRepositoryInterface;
use TwitchAnalytics\Interfaces\TwitchApiRepositoryInterface;

class GetTopOfTopsService
{
    public function __construct(
        private readonly DataBaseRepositoryInterface $dataBaseRepository,
        private readonly TwitchApiRepositoryInterface $twitchApiRepository,
        private readonly TokenManager $tokenManager
    ) {
    }
    public function getTopOfTops(?string $since): JsonResponse
    {
        $since = $since ?? '600';

        $token = $this->tokenManager->getToken();

        $topGames = $this->twitchApiRepository->getTopGames($token);

        $topGames = json_decode($topGames[0], true)['data'] ?? [];

        if (empty($topGames)) {
            return new JsonResponse([
                'error' => 'No top games data available.',
            ], 404);
        }

        $ultimaSolicitud = $this->dataBaseRepository->getUltimaSolicitud();

        if ((time() - $ultimaSolicitud) > $since) {
            $this->dataBaseRepository->clearCache();

            foreach ($topGames as $game) {
                $topStreamer = $this->twitchApiRepository->getTopStreamer($game, $token);

                $this->dataBaseRepository->insertarTopStreamer($topStreamer);
            }
        }

        $topStreamers = $this->dataBaseRepository->getTopStreamer();

        return new JsonResponse($topStreamers, 200);
    }
}
