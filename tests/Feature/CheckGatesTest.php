<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;

class CheckGatesTest extends TestCase
{
    public function test()
    {
        ErrorPrinter::$instance = null;

        $r = $this->artisan('check:gates')->run();

        $this->assertEquals(0, $r);
    }
}