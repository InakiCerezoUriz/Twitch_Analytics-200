<?php

namespace App\Http\Controllers\GetTopOfTops;

use App\Exceptions\EmptyOrInvalidSinceException;
use App\Services\GetTopOfTopsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetTopOfTopsController
{
    public function __construct(
        private readonly GetTopOfTopsValidator $validatorSince,
        private readonly GetTopOfTopsService $getTopOfTopsService
    ) {
    }

    public function getTopOfTops(Request $request): JsonResponse
    {
        try {
            $since = $this->validatorSince->validateSince($request->get('since'));

            return $this->getTopOfTopsService->getTopOfTops($since);
        } catch (EmptyOrInvalidSinceException $exception) {
            return new JsonResponse([
                'error' => $exception->getMessage(),
            ], 400);
        }
    }
}
