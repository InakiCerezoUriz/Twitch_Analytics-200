<?php

namespace App\Services;

use App\Repositories\DataBaseRepository;
use Illuminate\Http\JsonResponse;

class UserRegisterService
{
    public function __construct(
        private DataBaseRepository $dataBaseRepository
    ) {
        $this->dataBaseRepository = new DataBaseRepository();
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
