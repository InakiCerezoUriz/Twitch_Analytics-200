<?php

namespace TwitchAnalytics\Services;

use Illuminate\Http\JsonResponse;
use TwitchAnalytics\Infrastructure\TokenManager;
use TwitchAnalytics\Interfaces\DataBaseRepositoryInterface;
use TwitchAnalytics\Interfaces\TwitchApiRepositoryInterface;

class GetUserByIdService
{
    public function __construct(
        private readonly DataBaseRepositoryInterface $dataBaseRepository,
        private readonly TwitchApiRepositoryInterface $twitchApiRepository,
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
