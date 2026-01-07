<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Foundations\Reports\ComposerJsonReport;

class CheckEarlyTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        copy(__DIR__.'/CheckEarlyStubs/init.stub', $this->mainPath());
    }

    public function tearDown(): void
    {
        ErrorPrinter::$instance = null;
        ComposerJsonReport::$callback = null;
        @unlink($this->mainPath());
        parent::tearDown();
    }

    public function test()
    {
        $r = $this->artisan('check:early_returns')
            ->expectsQuestion(' Do you have committed everything in git?', true)
            ->expectsQuestion(' Do you want to flatten: <fg=yellow>app'.DIRECTORY_SEPARATOR.'early.php</>', true)
            ->run();

        $this->assertEquals(0, $r);
    }

    private function mainPath()
    {
        return app_path('early.php');
    }
}