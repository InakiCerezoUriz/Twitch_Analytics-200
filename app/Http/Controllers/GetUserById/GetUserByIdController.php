<?php

namespace App\Http\Controllers\GetUserById;

use App\Exceptions\EmptyOrInvalidIdException;
use App\Services\GetUserByIdService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class GetUserByIdController extends BaseController
{
    private GetUserByIdValidator $validatorId;
    private GetUserByIdService $getUserByIdService;

    public function __construct(
        GetUserByIdValidator $validatorId,
        GetUserByIdService $getUserByIdService
    ) {
        $this->validatorId        = $validatorId;
        $this->getUserByIdService = $getUserByIdService;
    }

    public function getUser(Request $request): JsonResponse
    {

        require_once __DIR__ . '/../../../../src/funcionesAuxiliares/conseguirToken.php';
        require_once __DIR__ . '/../../../../src/funcionesAuxiliares/comprobarExpiracion.php';
        require_once __DIR__ . '/../../../../src/funcionesAuxiliares/comprobarAuthorization.php';
        require_once __DIR__ . '/../../../../src/funcionesAuxiliares/iniciarCurl.php';

        //comprobarAuthorization();

        $authHeader = $request->header('Authorization');
        $token      = str_replace('Bearer ', '', $authHeader);

        if (!comprobarExpiracion($token)) {
            return new JsonResponse([
                'error' => 'Unauthorized. Token is invalid or has expired.',
            ], 401);
        }

        try {
            $id = $this->validatorId->validateId($request->get('id'));

            return $this->getUserByIdService->getUser($id);
        } catch (EmptyOrInvalidIdException $exception) {
            return new JsonResponse([
                'error' => $exception->getMessage(),
            ], 400);
        }
    }
}
