<?php

namespace TwitchAnalytics\Tests\app\Http\Controllers\GetUserById;

use App\Exceptions\EmptyOrInvalidIdException;
use App\Http\Controllers\GetUserById\GetUserByIdValidator;
use PHPUnit\Framework\TestCase;

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
