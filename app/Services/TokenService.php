<?php

namespace App\Services;

use App\Infrastructure\TokenManager;
use App\Interfaces\DataBaseRepositoryInterface;
use Illuminate\Http\JsonResponse;

class TokenService
{
    public function __construct(
        private readonly DataBaseRepositoryInterface $dataBaseRepository,
        private readonly TokenManager $tokenManager
    ) {
    }
    public function getToken(string $email, string $apiKey): JsonResponse
    {
        $usuario = $this->dataBaseRepository->getTokenFromDataBase($email);

        if (!$usuario) {
            return new JsonResponse([
                'error' => 'Unauthorized. API access token is invalid.',
            ], 401);
        }

        if ($usuario['api_key'] !== $apiKey || $usuario['email'] !== $email) {
            return new JsonResponse([
                'error' => 'Unauthorized. API access token is invalid.',
            ], 401);
        }

        if ($usuario['token'] === null || $usuario['fechaexpiracion'] < date('Y-m-d H:i:s')) {
            $nuevoToken = $this->tokenManager->generarToken();
            $this->dataBaseRepository->updateUserTokenInDataBase($nuevoToken, $usuario['email']);
            $resultado = ['token' => $nuevoToken['token']];
        } else {
            $resultado = ['token' => $usuario['token']];
        }

        return new JsonResponse($resultado, 200);
    }
}
