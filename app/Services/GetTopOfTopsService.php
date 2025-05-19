<?php

namespace App\Services;

use App\Infrastructure\TokenManager;
use App\Interfaces\DataBaseRepositoryInterface;
use App\Repositories\TwitchApiRepository;
use Illuminate\Http\JsonResponse;

class GetTopOfTopsService
{
    public function __construct(
        private readonly DataBaseRepositoryInterface $dataBaseRepository,
        private readonly TwitchApiRepository $twitchApiRepository,
        private readonly TokenManager $tokenManager,
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
