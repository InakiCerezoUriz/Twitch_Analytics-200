<?php

namespace TwitchAnalytics\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use TwitchAnalytics\Infrastructure\TokenManager;

class AuthorizationMiddleware
{
    public function __construct(
        private readonly TokenManager $tokenManager
    ) {
    }
    public function handle($request, Closure $next)
    {
        if ($request->header('Authorization') == null) {
            return new JsonResponse([
                'error' => 'Authorization header is missing.',
            ], 400);
        }

        $authHeader = $request->header('Authorization');
        $token      = str_replace('Bearer ', '', $authHeader);

        if (!$this->tokenManager->tokenActive($token)) {
            return new JsonResponse([
                'error' => 'Unauthorized. Token is invalid or expired.',
            ], 401);
        }

        return $next($request);
    }
}
