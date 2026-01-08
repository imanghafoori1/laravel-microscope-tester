<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\ImportAnalyzer\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Foundations\Reports\ComposerJsonReport;

class CheckDynamicWhereTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        ComposerJsonReport::$callback = null;
        copy(__DIR__.'/DynamicWhereStubs/dynamic-where-init.stub', $this->mainPath());
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
        $r = $this->artisan('check:dynamic_wheres')
            ->expectsConfirmation('Do you want to replace dynamic_wheres.php with new version of it?', 'yes')
            ->run();

        $this->assertEquals(0, $r);

        $this->assertEquals(
            file_get_contents(__DIR__.'/DynamicWhereStubs/dynamic-where-final.stub'),
            file_get_contents($this->mainPath())
        );

    }

    private function mainPath()
    {
        return app_path('dynamic_wheres.php');
    }
}