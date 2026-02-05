<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Features\CheckDD\CheckDD;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\SpyClasses\RoutePaths;

class CheckDdTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Color::$color = CheckDD::$cache = false;
        RoutePaths::$additionalFiles = [base_path('routes/web2.php')];
        copy(__DIR__.'/CheckDDStubs/init.stub', $this->tmpFileUnderTest());
        copy(__DIR__.'/CheckDDStubs/sample_route.stub', base_path('routes/web2.php'));
    }

    public function tearDown(): void
    {
        Color::$color = CheckDD::$cache = true;
        RoutePaths::$additionalFiles = [];
        @unlink(base_path('routes/web2.php'));
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test()
    {
        $this->artisan('check:dd')
            ->expectsOutput('Checking for debug functions...')
            ->expectsOutput("   1 Debug function found: 'dump'")
            ->expectsOutput("   4| dump('sss');")
            ->expectsOutput('at routes'.DIRECTORY_SEPARATOR.'web2.php:4')
            //
            ->expectsOutput("   2 Debug function found: 'dd'")
            ->expectsOutput("   8| DD('sfvsf');")
            ->expectsOutput('at app'.DIRECTORY_SEPARATOR.'dd.php:8')
            //
            ->expectsOutput("   3 Debug function found: 'dd'")
            ->expectsOutput("   9| dd('sfvsf');")
            ->expectsOutput('at app'.DIRECTORY_SEPARATOR.'dd.php:9')
            ->run();
    }

    private function tmpFileUnderTest()
    {
        return app_path('dd.php');
    }
}
