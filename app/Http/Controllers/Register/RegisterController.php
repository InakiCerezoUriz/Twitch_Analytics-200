<?php

namespace App\Http\Controllers\Register;

use App\Exceptions\EmptyParameterException;
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
        include_once __DIR__ . '/../../../../src/funcionesAuxiliares/conectarBBDD.php';
        include_once __DIR__ . '/../../../../src/funcionesAuxiliares/generarApiKey.php';

        try {
            $email = $this->validator->validateEmail($request->get('email'));

            return $this->userRegisterService->register($email);
        } catch (EmptyParameterException) {
            return new JsonResponse([
                'error' => 'The email is mandatory',
            ], 400);
        } catch (InvalidArgumentException) {
            return new JsonResponse([
                'error' => 'The email must be a valid email address',
            ], 400);
        }
    }
}
