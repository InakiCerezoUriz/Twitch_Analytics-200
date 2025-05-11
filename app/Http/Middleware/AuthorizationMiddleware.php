<?php

namespace App\Http\Middleware;

use App\Repositories\DataBaseRepository;
use Closure;
use Illuminate\Http\JsonResponse;

class ExampleMiddleware
{
    public function handle($request, Closure $next)
    {
        $dataBaseRepository = new DataBaseRepository();

        if (!isset($request->headers['Authorization'])) {
            return new JsonResponse([
                'error' => 'Authorization header is missing.',
            ], 400);
        }

        $authHeader = $request->header('Authorization');
        $token      = str_replace('Bearer ', '', $authHeader);

        if (!$dataBaseRepository->comprobarExpiracion($token)) {
            return new JsonResponse([
                'error' => 'Unauthorized. Token is invalid or has expired.',
            ], 401);
        }

        return $next($request);
    }


}
