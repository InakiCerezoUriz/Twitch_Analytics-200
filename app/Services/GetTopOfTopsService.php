<?php

namespace App\Services;

use App\Infrastructure\TokenManager;
use App\Interfaces\DataBaseRepositoryInterface;
use App\Interfaces\TwitchApiRepositoryInterface;
use Illuminate\Http\JsonResponse;

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
        $since = $since ?? 600;

        $token = $this->tokenManager->getToken();

        $topGames = $this->twitchApiRepository->getTopGames(3, $token);


        if (empty($topGames)) {
            return new JsonResponse([
                'error' => 'No top games data available.',
            ], 404);
        }

        $ultimaSolicitud = $this->dataBaseRepository->getUltimaSolicitud();

        //        if ((time() - $ultimaSolicitud) > $since) {
        //            $this->dataBaseRepository->clearCache();
        //        }

        $tops = $this->dataBaseRepository->getTops();

        $final = [];

        foreach ($tops as $top) {
            $final = $this->dataBaseRepository->obtenerInformacionJuego($top['game_name'], $top['user_name']);
        }

        return new JsonResponse($final, 200);
    }
}
