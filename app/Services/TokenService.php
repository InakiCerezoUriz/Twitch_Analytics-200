<?php

namespace App\Services;

use App\Infrastructure\TokenManager;
use App\Repositories\DataBaseRepository;
use Illuminate\Http\JsonResponse;

class TokenService
{
    private DataBaseRepository $dataBaseRepository;
    private TokenManager $tokenManager;

    public function __construct(
        DataBaseRepository $dataBaseRepository,
        TokenManager $tokenManager
    ) {
        $this->dataBaseRepository = $dataBaseRepository;
        $this->tokenManager       = $tokenManager;
    }
    public function getToken(string $email, string $api_key): JsonResponse
    {
        $usuario = $this->dataBaseRepository->getTokenFromDataBase($email);

        if (!$usuario) {
            return new JsonResponse([
                'error' => 'Unauthorized. API access token is invalid.',
            ], 401);
        }

        if ($usuario['api_key'] !== $api_key || $usuario['email'] !== $email) {
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
