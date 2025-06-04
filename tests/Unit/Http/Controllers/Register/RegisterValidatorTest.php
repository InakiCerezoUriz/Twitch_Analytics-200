<?php

namespace TwitchAnalytics\Tests\Unit\Http\Controllers\Register;

use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Exceptions\EmptyEmailParameterException;
use TwitchAnalytics\Exceptions\InvalidArgumentException;
use TwitchAnalytics\Http\Controllers\Register\RegisterValidator;

class RegisterValidatorTest extends TestCase
{
    /**
     * @test
     */
    public function givenValidEmailReturnsEmail(): void
    {
        $validator = new RegisterValidator();
        $email     = 'test@example.com';

        $result = $validator->validateEmail($email);

        $this->assertEquals($email, $result);
    }

    /**
     * @test
     */
    public function testGivenEmptyEmailReturnsErrorMessage(): void
    {
        $validator = new RegisterValidator();


        $this->expectException(EmptyEmailParameterException::class);

        $validator->validateEmail('');
    }

    /**
     * @test
     */
    public function testGivenNullEmailReturnsErrorMessage(): void
    {
        $validator = new RegisterValidator();

        $this->expectException(EmptyEmailParameterException::class);

        $validator->validateEmail(null);
    }

    /**
     * @test
     */
    public function testGivenInvalidEmailReturnsErrorMessage(): void
    {
        $validator = new RegisterValidator();

        $this->expectException(InvalidArgumentException::class);

        $validator->validateEmail('invalid-email');
    }
}
