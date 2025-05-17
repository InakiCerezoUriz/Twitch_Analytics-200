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
        require_once __DIR__ . '/../../../../src/funcionesAuxiliares/conseguirToken.php';
        require_once __DIR__ . '/../../../../src/funcionesAuxiliares/iniciarCurl.php';
        require_once __DIR__ . '/../../../../src/funcionesAuxiliares/comprobarExpiracion.php';
        require_once __DIR__ . '/../../../../src/funcionesAuxiliares/comprobarAuthorization.php';

        return $this->getStreamsService->getStreams();
    }
}
