<?php

namespace TwitchAnalytics\Tests\Unit\Http\Controllers\GetEnrichedStreams;

use PHPUnit\Framework\TestCase;
use TwitchAnalytics\Exceptions\InvalidLimitException;
use TwitchAnalytics\Http\Controllers\GetEnrichedStreams\GetEnrichedStreamsValidator;

class GetEnrichedStreamsValidatorTest extends TestCase
{
    /**
     * @test
     */
    public function givenMissingLimitThrowsException(): void
    {
        $validator = new GetEnrichedStreamsValidator();

        $this->expectException(InvalidLimitException::class);

        $validator->validateLimit(null);
    }

    /**
     * @test
     */
    public function givenInvalidLimitThrowsException(): void
    {
        $validator = new GetEnrichedStreamsValidator();

        $this->expectException(InvalidLimitException::class);

        $validator->validateLimit('hola');
    }

    /**
     * @test
     */
    public function givenNegativeLimitThrowsException(): void
    {
        $validator = new GetEnrichedStreamsValidator();

        $this->expectException(InvalidLimitException::class);

        $validator->validateLimit('-1');
    }

    /**
     * @test
     */
    public function givenLimitGreaterThan20ThrowsException(): void
    {
        $validator = new GetEnrichedStreamsValidator();

        $this->expectException(InvalidLimitException::class);

        $validator->validateLimit('21');
    }

    /**
     * @test
     */
    public function givenValidLimitReturnsLimit(): void
    {
        $validator = new GetEnrichedStreamsValidator();

        $response = $validator->validateLimit('10');

        $this->assertEquals('10', $response);
    }
}
