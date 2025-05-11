<?php

namespace App\Http\Controllers\Register;

use App\Exceptions\EmptyEmailParameterException;
use App\Exceptions\InvalidArgumentException;
use App\Services\UserRegisterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class RegisterController extends BaseController
{
    private RegisterValidator $validator;
    private UserRegisterService $userRegisterService;

    public function __construct(
        RegisterValidator $validator,
        UserRegisterService $userRegisterService
    ) {
        $this->validator           = $validator;
        $this->userRegisterService = $userRegisterService;
    }

    public function register(Request $request): JsonResponse
    {
        try {
            $email = $this->validator->validateEmail($request->get('email'));

            return $this->userRegisterService->register($email);
        } catch (EmptyEmailParameterException | InvalidArgumentException $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
