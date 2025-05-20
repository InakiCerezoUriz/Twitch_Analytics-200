<?php

namespace App\Services;

use App\Infrastructure\TokenManager;
use App\Interfaces\TwitchApiRepositoryInterface;
use Illuminate\Http\JsonResponse;

class GetStreamsService
{
    public function __construct(
        private readonly TwitchApiRepositoryInterface $twitchApiRepository,
        private readonly TokenManager $tokenManager
    ) {
    }
    public function getStreams(): JsonResponse
    {
        $token = $this->tokenManager->getToken();

        [$response, $httpCode] = $this->twitchApiRepository->getStreamsFromTwitchApi($token);
        switch ($httpCode) {
            case 200:
                $lista = [];
                foreach ($response as $stream) {
                    $lista[] = $stream->getStream();
                }

                return new JsonResponse($lista, 200);
            case 401:
                return new JsonResponse([
                    'error' => 'Unauthorized. Twitch access token is invalid or has expired.',
                ], 401);
        }

        return new JsonResponse([
            'error' => 'Internal Server error.',
        ], 500);
    }
}
