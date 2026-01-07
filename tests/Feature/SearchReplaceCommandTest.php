<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\ImportAnalyzer\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Foundations\Reports\ComposerJsonReport;

class SearchReplaceCommandTest extends TestCase
{
    public function tearDown(): void
    {
        ErrorPrinter::$instance = null;
        @unlink($this->mainPath());
        @unlink($this->mainPath());
        parent::tearDown();
    }

    public function test()
    {
        $r = $this->artisan('search_replace')->run();

        $this->assertEquals(0, $r);

        $this->assertFileExists($this->mainPath());
        @unlink($this->mainPath());
        copy(__DIR__.'/SearchReplaceCommandStub/init.stub', $this->mainPath());
        $this->artisan('search_replace')->run();
    }

    private function mainPath()
    {
        return base_path('search_replace.php');
    }
}