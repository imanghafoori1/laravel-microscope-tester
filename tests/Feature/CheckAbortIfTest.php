<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\ImportAnalyzer\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Foundations\Reports\ComposerJsonReport;

class CheckAbortIfTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        copy(__DIR__.'/CheckAbortIfStubs/init.stub', $this->mainPath());
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
        $r = $this->artisan('check:abort_if')
            ->expectsQuestion('Do you want to replace abort_if.php with new version of it?', true)
            ->run();

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckAbortIfStubs/expected.stub'),
            file_get_contents($this->mainPath())
        );

        $this->assertEquals(0, $r);
    }

    private function mainPath()
    {
        return app_path('abort_if.php');
    }
}