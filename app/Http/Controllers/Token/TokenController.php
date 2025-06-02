<?php

namespace TwitchAnalytics\Http\Controllers\Token;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use TwitchAnalytics\Exceptions\EmptyApiKeyException;
use TwitchAnalytics\Exceptions\EmptyEmailException;
use TwitchAnalytics\Exceptions\InvalidArgumentException;
use TwitchAnalytics\Services\TokenService;

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
