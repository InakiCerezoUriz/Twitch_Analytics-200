<?php

namespace App\Http\Controllers\GetStreams;

use App\Services\GetStreamsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

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
