<?php

namespace TwitchAnalytics\Tests\Unit\Http\Controllers\GetTopOfTops;

use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Exceptions\EmptyOrInvalidSinceException;
use TwitchAnalytics\Http\Controllers\GetTopOfTops\GetTopOfTopsValidator;

class GetTopOfTopsValidatorTest extends TestCase
{
    /**
     * @test
     */
    public function givenInvalidSinceThrowsException(): void
    {
        $validator = new GetTopOfTopsValidator();

        $this->expectException(EmptyOrInvalidSinceException::class);

        $validator->validateSince('hola');
    }

    /**
     * @test
     */
    public function givenNegativeSinceThrowsException(): void
    {
        $validator = new GetTopOfTopsValidator();

        $this->expectException(EmptyOrInvalidSinceException::class);

        $validator->validateSince('-1');
    }

    /**
     * @test
     */
    public function givenEmptySinceThrowsException(): void
    {
        $validator = new GetTopOfTopsValidator();

        $this->expectException(EmptyOrInvalidSinceException::class);

        $validator->validateSince('');
    }

    /**
     * @test
     */
    public function givenNullSinceReturnsNull(): void
    {
        $validator = new GetTopOfTopsValidator();

        $response = $validator->validateSince(null);

        $this->assertNull($response);
    }

    /**
     * @test
     */
    public function givenValidSinceReturnsSince(): void
    {
        $validator = new GetTopOfTopsValidator();

        $response = $validator->validateSince('1');

        $this->assertEquals('1', $response);
    }
}
