<?php

namespace App\Services;

use App\Repositories\TwitchApiRepository;
use Illuminate\Http\JsonResponse;

class GetStreamsService
{
    private TwitchApiRepository $twitchApiRepository;
    public function __construct(
        TwitchApiRepository $twitchApiRepository
    ) {
        $this->twitchApiRepository = $twitchApiRepository;
    }
    public function getStreams(): JsonResponse
    {
        [$response, $res] = $this->twitchApiRepository->getStreamsFromTwitchApi();
        switch ($res) {
            case 200:
                $data  = json_decode($response, true);
                $lista = [];
                for ($i = 0; $i < count($data['data']); $i++) {
                    $title     = $data['data'][$i]['title'];
                    $user_name = $data['data'][$i]['user_name'];
                    $lista[$i] = [
                        'title'     => $title,
                        'user_name' => $user_name,
                    ];
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
