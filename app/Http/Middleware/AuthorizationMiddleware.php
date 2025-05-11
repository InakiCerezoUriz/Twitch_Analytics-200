<?php

namespace App\Http\Middleware;

use App\Infrastructure\TokenManager;
use App\Repositories\DataBaseRepository;
use App\Repositories\TwitchApiRepository;
use Closure;
use Illuminate\Http\JsonResponse;

class AuthorizationMiddleware
{
    public function handle($request, Closure $next)
    {
        $tokenManager = new TokenManager(new DataBaseRepository(), new TwitchApiRepository());

        if ($request->header('Authorization') == null) {
            return new JsonResponse([
                'error' => 'Authorization header is missing.',
            ], 400);
        }

        $authHeader = $request->header('Authorization');
        $token      = str_replace('Bearer ', '', $authHeader);

        if (!$tokenManager->tokenActive($token)) {
            return new JsonResponse([
                'error' => 'Unauthorized. Token is invalid or has expired.',
            ], 401);
        }

        return $next($request);
    }
}
