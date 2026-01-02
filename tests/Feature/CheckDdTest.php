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
        CheckDD::$cache = false;
        Color::$color = false;
        RoutePaths::$additionalFiles = [base_path('routes/web2.php')];
        copy(__DIR__.'/CheckDDStubs/init.stub', $this->tmpFileUnderTest());
        copy(__DIR__.'/CheckDDStubs/sample_route.stub', base_path('routes/web2.php'));
    }

    public function tearDown(): void
    {
        CheckDD::$cache = true;
        Color::$color = true;
        RoutePaths::$additionalFiles = [];
        @unlink(base_path('routes/web2.php'));
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test()
    {
        $this->artisan('check:dd')
            ->expectsOutput('Checking dd...')
            ->expectsOutput('   1 Debug function found: ')
            ->expectsOutputToContain('   dd')
            ->expectsOutputToContain('at app'.DIRECTORY_SEPARATOR.'dd.php:8')
            //
            ->expectsOutput('   2 Debug function found: ')
            ->expectsOutput('   dd')
            ->expectsOutput('at app'.DIRECTORY_SEPARATOR.'dd.php:9')
            //
            ->expectsOutputToContain('   3 Debug function found: ')
            ->expectsOutputToContain('   dump')
            ->expectsOutputToContain('at routes'.DIRECTORY_SEPARATOR.'web2.php:4')
            ->run();
    }

    private function tmpFileUnderTest()
    {
        return app_path('dd.php');
    }
}
