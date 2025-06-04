<?php

namespace TwitchAnalytics\Services;

use Illuminate\Http\JsonResponse;
use TwitchAnalytics\Interfaces\DataBaseRepositoryInterface;

class UserRegisterService
{
    public function __construct(
        private readonly DataBaseRepositoryInterface $dataBaseRepository
    ) {
    }

    public function register(string $email): JsonResponse
    {
        $apiKey = $this->dataBaseRepository->getApiKey($email);
        if (!empty($apiKey)) {
            $this->dataBaseRepository->updateApiKey($apiKey, $email);
        } else {
            $this->dataBaseRepository->insertApiKey($email, $apiKey);
        }
        return new JsonResponse([
            'api_key' => $apiKey,
        ], 200);
    }
}
