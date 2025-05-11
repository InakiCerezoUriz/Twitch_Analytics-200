<?php

namespace TwitchAnalytics\Tests\app\Http\Controllers\GetStreams;

use PHPUnit\Framework\TestCase;

class GetStreamsControllerTest extends TestCase
{
    /**
     * @test
     */
    public function testGetStreams(): void
    {
        $response = $this->call('GET', '/analytics/streams');
        $response->assertStatus(200);
    }
}
