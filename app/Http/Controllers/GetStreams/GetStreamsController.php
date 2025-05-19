<?php

namespace App\Http\Controllers\GetStreams;

use App\Services\GetStreamsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class GetStreamsController extends BaseController
{
    private GetStreamsService $getStreamsService;
    public function __construct(
        GetStreamsService $getStreamsService
    ) {
        $this->getStreamsService = $getStreamsService;
    }
    public function getStreams(Request $request): JsonResponse
    {
        return $this->getStreamsService->getStreams();
    }
}
