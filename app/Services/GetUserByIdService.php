<?php

namespace App\Services;

use App\Infrastructure\TokenManager;
use App\Repositories\DataBaseRepository;
use App\Repositories\TwitchApiRepository;
use Illuminate\Http\JsonResponse;

class GetUserByIdService
{
    private DataBaseRepository $dataBaseRepository;
    private TwitchApiRepository $twitchApiRepository;
    private TokenManager $tokenManager;

    public function __construct(
        DataBaseRepository $dataBaseRepository,
        TwitchApiRepository $twitchApiRepository,
        TokenManager $tokenManager
    ) {
        $this->dataBaseRepository  = $dataBaseRepository;
        $this->twitchApiRepository = $twitchApiRepository;
        $this->tokenManager        = $tokenManager;
    }
    public function getUser(string $id): JsonResponse
    {
        $result = $this->dataBaseRepository->getUserFromDataBase($id);

        $token = $this->tokenManager->getToken();

        if (empty($result)) {
            list($response, $httpCode) = $this->twitchApiRepository->getUserFromTwitchApi($id, $token);

            $data = json_decode($response, true);
            switch ($httpCode) {
                case 200:
                    if (empty($data['data'])) {
                        return new JsonResponse([
                            'error' => 'User not found.',
                        ], 404);
                    } else {
                        $this->dataBaseRepository->insertUserInDataBase($data['data'][0]);
                        return new JsonResponse($data['data'][0], 200);
                    }
                    // no break
                case 401:
                    return new JsonResponse([
                        'error' => 'Unauthorized. Twitch access token is invalid or has expired.',
                    ], 401);
                case 500:
                    return new JsonResponse([
                        'error' => 'Internal Server error.',
                    ], 500);
            }
        }
        return new JsonResponse($result, 200);
    }
}
