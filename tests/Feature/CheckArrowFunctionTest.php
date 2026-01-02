<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\ImportAnalyzer\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Foundations\Reports\ComposerJsonReport;

class CheckArrowFunctionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        ErrorPrinter::$instance = null;
        copy(__DIR__.'/CheckArrowFunctionStub/init.stub', $this->mainPath());
    }

    public function tearDown(): void
    {
        ComposerJsonReport::$callback = null;
        @unlink($this->mainPath());
        parent::tearDown();
    }

    public function test()
    {
        $r = $this->artisan('check:arrow_functions')
            ->expectsQuestion('Do you want to replace arrow.php with new version of it?', 'yes')
            ->run();

        $this->assertEquals(0, $r);

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckArrowFunctionStub/expected.stub'),
            file_get_contents($this->mainPath())
        );
    }

    private function mainPath()
    {
        return app_path('arrow.php');
    }
}