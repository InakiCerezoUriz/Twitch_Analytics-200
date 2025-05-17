<?php

namespace App\Services;

use App\Infrastructure\TokenManager;
use App\Repositories\DataBaseRepository;
use App\Repositories\TwitchApiRepository;
use Illuminate\Http\JsonResponse;

class GetUserByIdService
{
    public function __construct(
        private readonly DataBaseRepository $dataBaseRepository,
        private readonly TwitchApiRepository $twitchApiRepository,
        private readonly TokenManager $tokenManager
    ) {
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
                case 400:
                    return new JsonResponse([
                        'error' => "Invalid or missing 'id' parameter.",
                    ], 400);
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
