<?php

namespace TwitchAnalytics\Http\Controllers\GetEnrichedStreams;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use TwitchAnalytics\Exceptions\InvalidLimitException;
use TwitchAnalytics\Services\GetEnrichedStreamsService;

class GetEnrichedStreamsController extends BaseController
{
    private GetEnrichedStreamsValidator $validatorStreams;
    private GetEnrichedStreamsService $getEnrichedStreamsService;


    public function __construct(
        GetEnrichedStreamsService $getEnrichedStreamsService,
        GetEnrichedStreamsValidator $validatorStreams
    ) {
        $this->getEnrichedStreamsService = $getEnrichedStreamsService;
        $this->validatorStreams          = $validatorStreams;
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
