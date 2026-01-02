<?php

use Illuminate\Foundation\Testing\TestCase;

class CheckEventsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function test()
    {
        $r = $this->artisan('check:events')->run();

        $this->assertEquals(0, $r);
    }
}