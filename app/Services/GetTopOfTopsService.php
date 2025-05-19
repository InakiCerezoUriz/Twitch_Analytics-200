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

        $final = [];

        //        if ((time() - $ultimaSolicitud) < $since) {
        //            foreach ($topGames as $game) {
        //                $final[] = $this->dataBaseRepository->obtenerInformacionJuego($game['game_name']);
        //            }
        //            return new JsonResponse($final, 200);
        //        }

        $this->dataBaseRepository->clearCache();

        foreach ($topGames as $game) {
            $this->dataBaseRepository->insertTopsInDataBase($game);
            $final[] = $this->dataBaseRepository->obtenerInformacionJuego($game['game_name'], $game['user_name']);
        }

        return new JsonResponse($final, 200);
    }
}
