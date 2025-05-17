<?php

namespace TwitchAnalytics\Tests\app\Http\Controllers\Register;

use App\Exceptions\EmptyEmailParameterException;
use App\Exceptions\InvalidArgumentException;
use App\Http\Controllers\Register\RegisterValidator;
use PHPUnit\Framework\TestCase;

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
