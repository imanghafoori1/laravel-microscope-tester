<?php

use Illuminate\Foundation\Testing\TestCase;

class CheckAllTest extends TestCase
{
    public function test()
    {
        $r = $this->artisan('check:all')->run();

        $this->assertEquals(0, $r);
    }
}
