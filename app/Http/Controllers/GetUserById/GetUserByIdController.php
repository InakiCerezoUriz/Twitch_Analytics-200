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
