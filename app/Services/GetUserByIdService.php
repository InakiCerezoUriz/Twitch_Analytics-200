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
        $user = $this->dataBaseRepository->getUserFromDataBase($id);

        $token = $this->tokenManager->getToken();
        if ($user == null) {
            list($user, $httpCode) = $this->twitchApiRepository->getUserFromTwitchApi($id, $token);

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

            if ($httpCode == 200 && empty($user)) {
                return new JsonResponse([
                    'error' => 'User not found.',
                ], 404);
            }
            $this->dataBaseRepository->insertUserInDataBase($user);
        }

        return new JsonResponse($user->getInfo(), 200);
    }
}
