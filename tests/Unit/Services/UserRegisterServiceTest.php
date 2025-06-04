<?php

namespace TwitchAnalytics\Tests\Unit\Services;

use Illuminate\Http\JsonResponse;
use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Interfaces\DataBaseRepositoryInterface;
use TwitchAnalytics\Services\UserRegisterService;

class UserRegisterServiceTest extends TestCase
{
    private DataBaseRepositoryInterface $dataBaseRepository;
    private UserRegisterService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dataBaseRepository = $this->createMock(DataBaseRepositoryInterface::class);
        $this->service            = new UserRegisterService($this->dataBaseRepository);
    }

    /**
     * @test
     */
    public function givenExistingApiKeyUpdatesIt(): void
    {
        $email          = 'existing@mail.com';
        $existingApiKey = 'valid-key';

        $this->dataBaseRepository->method('getApiKey')
            ->with($email)
            ->willReturn($existingApiKey);

        $this->dataBaseRepository->expects($this->once())
            ->method('updateApiKey')
            ->with($existingApiKey, $email);

        $this->dataBaseRepository->expects($this->never())
            ->method('insertApiKey');

        $response = $this->service->register($email);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['api_key' => $existingApiKey], $response->getData(true));
    }

    /**
     * @test
     */
    public function givenNoApiKeyInsertsNewOne(): void
    {
        $email = 'new@mail.com';

        $this->dataBaseRepository->method('getApiKey')
            ->with($email)
            ->willReturn(null);

        $this->dataBaseRepository->expects($this->once())
            ->method('insertApiKey')
            ->with($email, null);

        $this->dataBaseRepository->expects($this->never())
            ->method('updateApiKey');

        $response = $this->service->register($email);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['api_key' => null], $response->getData(true));
    }
}
