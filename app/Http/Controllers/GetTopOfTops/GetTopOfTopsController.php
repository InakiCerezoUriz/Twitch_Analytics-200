<?php

namespace TwitchAnalytics\Http\Controllers\GetTopOfTops;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use TwitchAnalytics\Exceptions\EmptyOrInvalidSinceException;
use TwitchAnalytics\Services\GetTopOfTopsService;

class GetTopOfTopsController extends BaseController
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
