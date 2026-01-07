<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\ImportAnalyzer\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Foundations\Reports\ComposerJsonReport;

class CheckStringyClassTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        ComposerJsonReport::$callback = null;
        copy(__DIR__.'/CheckStringyClassStubs/init.stub', $this->mainPath());
    }

    public function tearDown(): void
    {
        ErrorPrinter::$instance = null;
        @unlink($this->mainPath());
        parent::tearDown();
    }

    public function test()
    {
        $r = $this->artisan('check:stringy_classes')
            ->expectsConfirmation("Replace: <fg=blue>'App\\Models\\User'</> with <fg=blue>::class</> version of it?", 'yes')
            ->run();
        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckStringyClassStubs/expect.stub'),
            file_get_contents($this->mainPath())
        );

        $this->assertEquals(1, $r);
    }

    private function mainPath()
    {
        return app_path('stringy.php');
    }
}