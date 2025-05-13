<?php

namespace App\Services;

use App\Repositories\TwitchApiRepository;
use Illuminate\Http\JsonResponse;

class GetEnrichedStreamsService
{
    private TwitchApiRepository $twitchApiRepository;
    public function __construct(
        TwitchApiRepository $twitchApiRepository
    ) {
        $this->twitchApiRepository = $twitchApiRepository;
    }

    public function getEnriched(string $limit): JsonResponse
    {
        list($response, $httpCode) = $this->twitchApiRepository->getStreamsFromTwitchApi();

        if ($httpCode !== 200) {
            $this->handleError($httpCode);
        }

        $data = json_decode($response, true);
        $lista = $this->buildEnrichedStreamList($data['data'], $limit);

        return new JsonResponse($lista, 200);
    }

    function handleError(int $res): JsonResponse
    {
        switch ($res) {
            case 400:
                return new JsonResponse([
                    'error' => "Invalid or missing 'limit' parameter.",
                ], 400);
            case 401:
                return new JsonResponse([
                    'error' => 'Unauthorized. Twitch access token is invalid or has expired.',
                ], 401);
            default:
                return new JsonResponse([
                    'error' => 'Internal Server error.',
                ], 500);
        }
    }
    function buildEnrichedStreamList(array $streams, int $limit): array
    {
        $lista = [];
        for ($i = 0; $i < $limit && isset($streams[$i]); $i++) {
            $user_id  = $streams[$i]['user_id'];
            $userData = $this->twitchApiRepository->getUserData($user_id);

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