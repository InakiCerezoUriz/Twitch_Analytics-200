<?php

namespace App\Http\Controllers\Token;

use App\Exceptions\EmptyApiKeyException;
use App\Exceptions\EmptyEmailException;
use App\Exceptions\InvalidArgumentException;
use App\Services\TokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PDO;

class TokenController
{
    private TokenValidator $validator;
    private TokenService $TokenService;

    public function __construct(
        TokenValidator $validator,
        TokenService $TokenService
    ) {
        $this->validator = $validator;
        $this->TokenService = $TokenService;
    }
    public function getToken(Request $request): JsonResponse
    {
        include_once __DIR__ . '/../../../../src/funcionesAuxiliares/conectarBBDD.php';
        include_once __DIR__ . '/../../../../src/funcionesAuxiliares/generarToken.php';

        try {
            $email   = $this->validator->validateEmail($request->get('email'));
            $api_key = $this->validator->validateApiKey($request->get('api_key'));

            return $this->TokenService->getToken($email, $api_key);
        } catch (EmptyEmailException | InvalidArgumentException | EmptyApiKeyException $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
