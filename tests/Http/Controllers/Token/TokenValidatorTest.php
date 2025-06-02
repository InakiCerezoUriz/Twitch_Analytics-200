<?php

namespace TwitchAnalytics\Tests\Http\Controllers\Token;

use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Exceptions\EmptyApiKeyException;
use TwitchAnalytics\Exceptions\EmptyEmailException;
use TwitchAnalytics\Exceptions\InvalidArgumentException;
use TwitchAnalytics\Http\Controllers\Token\TokenValidator;

class TokenValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new TokenValidator();
    }

    /**
     * @test
     */
    public function givenMissingEmailThrowsException(): void
    {
        $this->expectException(EmptyEmailException::class);

        $this->validator->validateEmail('');
    }

    /**
     * @test
     */
    public function givenInvalidEmailThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->validator->validateEmail('invalid-email');
    }

    /**
     * @test
     */
    public function givenValidEmailReturnEmail(): void
    {
        $email = 'test@example.com';

        $response = $this->validator->validateEmail($email);

        $this->assertEquals($email, $response);
    }

    /**
     * @test
     */
    public function givenEmptyApiKeyThrowsException(): void
    {
        $this->expectException(EmptyApiKeyException::class);

        $this->validator->validateApiKey('');
    }

    /**
     * @test
     */
    public function givenValidApiKeyReturnsApiKey(): void
    {
        $api_key = 'valid-api-key';

        $response = $this->validator->validateApiKey($api_key);

        $this->assertEquals($api_key, $response);
    }
}
