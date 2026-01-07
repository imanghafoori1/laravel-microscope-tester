<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Foundations\Reports\ComposerJsonReport;

class CheckExtraSemiColonsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        ComposerJsonReport::$callback = null;
        ErrorPrinter::$instance = null;
        parent::tearDown();
    }

    public function test()
    {
        $r = $this->artisan('check:extra_semi_colons')->run();

        $this->assertEquals(0, $r);
    }
}