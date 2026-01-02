<?php

use Illuminate\Foundation\Testing\TestCase;

class CheckExtraSemiColonsTest extends TestCase
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
        $r = $this->artisan('check:extra_semi_colons')->run();

        $this->assertEquals(0, $r);
    }
}