<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\ImportAnalyzer\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Foundations\Reports\ComposerJsonReport;

class SearchReplaceCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        ErrorPrinter::$instance = null;
    }

    public function tearDown(): void
    {
        @unlink($this->mainPath());
        parent::tearDown();
    }

    public function test()
    {
        $r = $this->artisan('search_replace')->run();

        $this->assertEquals(0, $r);

        $this->assertFileExists($this->mainPath());
    }

    private function mainPath()
    {
        return base_path('search_replace.php');
    }
}