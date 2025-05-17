<?php

namespace App\Services;

use App\Infrastructure\TokenManager;
use App\Repositories\TwitchApiRepository;
use Illuminate\Http\JsonResponse;

class GetEnrichedStreamsService
{
    private TwitchApiRepository $twitchApiRepository;
    private TokenManager $tokenManager;

    public function __construct(
        TwitchApiRepository $twitchApiRepository,
        TokenManager $tokenManager
    ) {
        $this->twitchApiRepository = $twitchApiRepository;
        $this->tokenManager        = $tokenManager;
    }

    public function getEnriched(string $limit): JsonResponse
    {
        $token = $this->tokenManager->getToken();

        list($enrichedStreams, $httpCode) = $this->twitchApiRepository->getEnrichedStreamsFromTwitchApi($token, $limit);

        if ($httpCode == 401) {
            return new JsonResponse([
                'error' => 'Unauthorized. Twitch access token is invalid or has expired.',
            ], 401);
        }
        if ($httpCode == 500) {
            return new JsonResponse([
                'error' => 'Internal Server error.',
            ], 500);
        }

        return new JsonResponse($enrichedStreams, 200);
    }
}
