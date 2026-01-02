<?php

use Illuminate\Foundation\Testing\TestCase;

class CheckStatsTest extends TestCase
{
    public function test()
    {
        $r = $this->artisan('check:stats')->run();

        $this->assertEquals(0, $r);
    }
}