<?php

namespace TwitchAnalytics\Tests\Http\Controllers\GetUserById;

use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Exceptions\EmptyOrInvalidIdException;
use TwitchAnalytics\Http\Controllers\GetUserById\GetUserByIdValidator;

class GetUserByIdValidatorTest extends TestCase
{
    /**
     * @test
     */
    public function givenMissingIdThrowsException(): void
    {
        $validator = new GetUserByIdValidator();

        $this->expectException(EmptyOrInvalidIdException::class);

        $validator->validateId(null);
    }

    /**
     * @test
     */
    public function givenInvalidIdThrowsException(): void
    {
        $validator = new GetUserByIdValidator();

        $this->expectException(EmptyOrInvalidIdException::class);

        $validator->validateId('hola');
    }

    /**
     * @test
     */
    public function givenNegativeIdThrowsException(): void
    {
        $validator = new GetUserByIdValidator();

        $this->expectException(EmptyOrInvalidIdException::class);

        $validator->validateId('-1');
    }

    /**
     * @test
     */
    public function givenValidIdReturnsId(): void
    {
        $validator = new GetUserByIdValidator();

        $response = $validator->validateId('1');

        $this->assertEquals('1', $response);
    }
}
