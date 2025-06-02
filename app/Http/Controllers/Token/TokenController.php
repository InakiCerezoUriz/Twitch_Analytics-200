<?php

namespace App\Http\Controllers\Token;

use App\Exceptions\EmptyApiKeyException;
use App\Exceptions\EmptyEmailException;
use App\Exceptions\InvalidArgumentException;
use App\Services\TokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class TokenController extends BaseController
{
    public function __construct(
        private readonly TokenValidator $validator,
        private readonly TokenService $TokenService
    ) {
    }
    public function getToken(Request $request): JsonResponse
    {
        try {
            $email  = $this->validator->validateEmail($request->get('email'));
            $apiKey = $this->validator->validateApiKey($request->get('api_key'));

            return $this->TokenService->getToken($email, $apiKey);
        } catch (EmptyEmailException | InvalidArgumentException | EmptyApiKeyException $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
