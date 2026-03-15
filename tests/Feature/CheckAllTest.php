<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckAllTest extends TestCase
{
    public function test()
    {
        Console::recoredWrites();

        $r = $this->artisan('check:all')->run();

        $this->assertEquals(0, $r);
    }
}
