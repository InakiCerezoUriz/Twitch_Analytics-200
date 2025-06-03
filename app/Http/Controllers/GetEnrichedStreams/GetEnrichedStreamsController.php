<?php

namespace TwitchAnalytics\Http\Controllers\GetEnrichedStreams;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use TwitchAnalytics\Exceptions\InvalidLimitException;
use TwitchAnalytics\Services\GetEnrichedStreamsService;

class GetEnrichedStreamsController extends BaseController
{
    public function __construct(
        private readonly GetEnrichedStreamsService $getEnrichedStreamsService,
        private readonly GetEnrichedStreamsValidator $validatorStreams
    ) {
    }


    public function getEnriched(Request $request): JsonResponse
    {
        try {
            $limit = $this->validatorStreams->validateLimit($request->get('limit'));

            return $this->getEnrichedStreamsService->getEnriched($limit);
        } catch (InvalidLimitException $exception) {
            return new JsonResponse([
                'error' => $exception->getMessage(),
            ], 400);
        }
    }
}
