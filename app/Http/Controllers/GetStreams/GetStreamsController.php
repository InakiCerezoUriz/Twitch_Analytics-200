<?php

namespace TwitchAnalytics\Http\Controllers\GetStreams;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use TwitchAnalytics\Services\GetStreamsService;

class GetStreamsController extends BaseController
{
    public function __construct(
        private readonly GetStreamsService $getStreamsService
    ) {
    }
    public function getStreams(Request $request): JsonResponse
    {
        return $this->getStreamsService->getStreams();
    }
}
