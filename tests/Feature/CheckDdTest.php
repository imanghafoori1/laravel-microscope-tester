<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\ImportAnalyzer\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\SpyClasses\RoutePaths;

class CheckDdTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        RoutePaths::$additionalFiles = [base_path('routes/web2.php')];
        RoutePaths::$providers = ['App\\Provider'];
        copy(__DIR__.'/CheckDDStubs/init.stub', $this->mainPath());
        copy(__DIR__.'/CheckDDStubs/sample_route.stub', base_path('routes/web2.php'));
        copy(__DIR__.'/CheckDDStubs/provider.stub', app_path('Provider.php'));
    }

    public function tearDown(): void
    {
        ErrorPrinter::$instance = null;
        RoutePaths::$providers = [];
        RoutePaths::$additionalFiles = [];
        @unlink(base_path('routes/web2.php'));
        @unlink(app_path('Provider.php'));
        @unlink($this->mainPath());
        parent::tearDown();
    }

    public function test()
    {
        $this->artisan('check:dd')
            ->expectsOutputToContain('Checking dd...')
            ->expectsOutputToContain('Debug function found:')
            ->expectsOutputToContain('dd')
            ->expectsOutputToContain('dump')
            ->run();
    }

    private function mainPath()
    {
        return app_path('dd.php');
    }
}
