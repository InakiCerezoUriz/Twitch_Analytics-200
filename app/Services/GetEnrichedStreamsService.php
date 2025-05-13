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

        list($response, $httpCode) = $this->twitchApiRepository->getStreamsFromTwitchApi($token);

        if ($httpCode !== 200) {
            return $this->handleError($httpCode);
        }

        $data  = json_decode($response, true);
        $lista = $this->buildEnrichedStreamList($data['data'], $limit, $token);

        return new JsonResponse($lista, 200);
    }

    private function handleError(int $res): JsonResponse
    {
        return $res == 401 ? new JsonResponse([
            'error' => 'Unauthorized. Twitch access token is invalid or has expired.',
        ], 401) : new JsonResponse([
            'error' => 'Internal Server error.',
        ], 500);
    }
    private function buildEnrichedStreamList(array $streams, int $limit, string $token): array
    {
        $lista = [];
        for ($i = 0; $i < $limit && isset($streams[$i]); $i++) {
            $user_id  = $streams[$i]['user_id'];
            $userData = $this->twitchApiRepository->getUserData($user_id, $token);

            $lista[] = [
                'stream_id'         => $streams[$i]['id'],
                'user_id'           => $user_id,
                'user_name'         => $streams[$i]['user_name'],
                'viewer_count'      => $streams[$i]['viewer_count'],
                'title'             => $streams[$i]['title'],
                'user_display_name' => $userData['display_name']      ?? null,
                'profile_image_url' => $userData['profile_image_url'] ?? null,
            ];
        }

        return $lista;
    }
}
