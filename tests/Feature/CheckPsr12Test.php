<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;

class CheckPsr12Test extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        copy(__DIR__.'/CheckPsr12Stub/init.stub', $this->mainPath());
    }

    public function tearDown(): void
    {
        ErrorPrinter::$instance = null;
        @unlink($this->mainPath());
        parent::tearDown();
    }

    public function test()
    {
        $r = $this->artisan('check:psr12')
            ->expectsQuestion('Do you have committed everything in git?', 'yes')
            ->run();

        $this->assertEquals(0, $r);
    }

    private function mainPath()
    {
        return app_path('Psr12.php');
    }
}