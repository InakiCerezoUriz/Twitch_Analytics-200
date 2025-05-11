<?php

namespace App\Services;

use App\Repositories\DataBaseRepository;
use Illuminate\Http\JsonResponse;
use PDO;

class TokenService
{
    private DataBaseRepository $dataBaseRepository;

    public function __construct(
        DataBaseRepository $dataBaseRepository
    ) {
        $this->dataBaseRepository = $dataBaseRepository;
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
            $nuevoToken = generarToken();
            $this->dataBaseRepository->updateUserTokenInDataBase($nuevoToken, $usuario['email']);
            $resultado = ['token' => $nuevoToken['token']];
        } else {
            $resultado = ['token' => $usuario['token']];
        }

        return new JsonResponse($resultado, 200);
    }
}
