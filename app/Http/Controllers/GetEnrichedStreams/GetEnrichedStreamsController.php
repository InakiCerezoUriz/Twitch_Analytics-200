<?php

namespace App\Http\Controllers\GetEnrichedStreams;

use App\Exceptions\InvalidLimitException;
use App\Services\GetEnrichedStreamsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

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
            $limit = $this->validatorStreams->validateStream($request->get('limit'));

            return $this->getEnrichedStreamsService->getEnriched($limit);
        } catch (InvalidLimitException $exception) {
            return new JsonResponse([
                'error' => $exception->getMessage(),
            ], 400);
        }
    }
}
