<?php

namespace App\Services;

use App\Infrastructure\TokenManager;
use App\Interfaces\TwitchApiRepositoryInterface;
use Illuminate\Http\JsonResponse;

class GetEnrichedStreamsService
{
    public function __construct(
        private readonly TwitchApiRepositoryInterface $twitchApiRepository,
        private readonly TokenManager $tokenManager
    ) {
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
