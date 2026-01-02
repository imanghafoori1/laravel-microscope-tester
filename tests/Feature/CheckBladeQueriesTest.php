<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\ImportAnalyzer\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Foundations\Reports\ComposerJsonReport;

class CheckBladeQueriesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        @mkdir(resource_path());
        @mkdir(resource_path('views'));
        copy(__DIR__.'/CheckBladeQueriesStubs/init.stub', $this->mainPath());
        ComposerJsonReport::$callback = null;
    }

    public function tearDown(): void
    {
        ErrorPrinter::$instance = null;
        ComposerJsonReport::$callback = null;
        @unlink($this->mainPath());
        @rmdir(resource_path('views'));
        @rmdir(resource_path());
        parent::tearDown();
    }

    public function test()
    {
        $ds = DIRECTORY_SEPARATOR;
        $r = $this->artisan('check:blade_queries')
            ->expectsOutputToContain('\App\Models\User  <=== DB query in blade file')
            ->expectsOutputToContain('Query in blade file: ')
            ->expectsOutputToContain("at resources{$ds}views{$ds}blade_queries.blade.php:4")
            ->run();

        $this->assertEquals(1, $r);
    }

    private function mainPath()
    {
        return resource_path('views/blade_queries.blade.php');
    }
}
