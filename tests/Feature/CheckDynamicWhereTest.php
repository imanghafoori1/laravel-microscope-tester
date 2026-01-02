<?php

use Illuminate\Foundation\Testing\TestCase;

class CheckDynamicWhereTest extends TestCase
{
    public function test()
    {
        $r = $this->artisan('check:dynamic_wheres')->run();

        $this->assertEquals(0, $r);
    }
}