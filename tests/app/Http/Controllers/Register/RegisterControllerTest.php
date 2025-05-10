<?php

namespace TwitchAnalytics\Tests\app\Http\Controllers\Register;

use App\Http\Controllers\Register\RegisterController;
use App\Http\Controllers\Register\RegisterValidator;
use App\Services\UserRegisterService;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class RegisterControllerTest extends TestCase
{
    /**
     * @test
     */
    public function gets400WhenEmailParameterIsMissing(): void
    {
        $request   = new Request();
        $validator = new RegisterValidator();

        $mockService = $this->createMock(UserRegisterService::class);
        $controller  = new RegisterController($validator, $mockService);

        $response     = $controller->register($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([
            'error' => 'The email is mandatory',
        ], $responseData);
    }

    /**
     * @test
     */
    public function gets400WhenEmailParameterIsInvalid(): void
    {
        $request   = new Request(['email' => 'invalid-email']);
        $validator = new RegisterValidator();

        $mockService = $this->createMock(UserRegisterService::class);
        $controller  = new RegisterController($validator, $mockService);

        $response     = $controller->register($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([
            'error' => 'The email must be a valid email address',
        ], $responseData);
    }

    /**
     * @test
     */
    public function gets200WhenEmailParameterValid(): void
    {
        $request   = new Request(['email' => 'test@example.com']);
        $validator = new RegisterValidator();

        $mockResponse = response()->json(['apiKey' => 'api_key_value'], 200);
        $mockService  = $this->createMock(UserRegisterService::class);
        $mockService->method('register')
            ->willReturn($mockResponse);

        $controller = new RegisterController($validator, $mockService);

        $response     = $controller->register($request);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([
            'apiKey' => 'api_key_value',
        ], $responseData);
    }
}
