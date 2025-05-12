<?php

namespace App\Http\Controllers\GetEnrichedStreams;

use App\Exceptions\EmptyOrInvalidLimitException;
use App\Services\GetEnrichedStreamsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class GetEnrichedStreamsController extends BaseController
{
    private GetEnrichedStreamsValidator $validatorStreams;
    private GetEnrichedStreamsService $getEnrichedStreamsService;


    public function __construct(GetEnrichedStreamsService $getEnrichedStreamsService, GetEnrichedStreamsValidator $validatorStreams)
    {
        $this->getEnrichedStreamsService = $getEnrichedStreamsService;
        $this->validatorStreams = $validatorStreams;
    }


    function getEnriched(Request $request): JsonResponse
    {
        require_once __DIR__ . '/../../../../src/funcionesAuxiliares/conseguirToken.php';
        require_once __DIR__ . '/../../../../src/funcionesAuxiliares/comprobarExpiracion.php';
        require_once __DIR__ . '/../../../../src/funcionesAuxiliares/comprobarAuthorization.php';

        //comprobarAuthorization();

        $authHeader = $request->header('Authorization');
        $token      = str_replace('Bearer ', '', $authHeader);

        if (!comprobarExpiracion($token)) {
            return new JsonResponse([
                'error' => 'Unauthorized. Token is invalid or has expired.',
            ], 401);
        }

        try{
            $limit = $this->validatorStreams->validateStream($request->get('limit'));

            return $this->getEnrichedStreamsService->getEnriched($limit);
        } catch (EmptyOrInvalidLimitException $exception) {
            return new JsonResponse([
                'error' => $exception->getMessage(),
            ], 400);
        }

    }
}