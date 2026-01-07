<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\SpyClasses\RoutePaths;

class CheckDdTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        RoutePaths::$paths = [base_path('routes/web.php')];
        copy(__DIR__.'/CheckDDStubs/init.stub', $this->mainPath());
    }

    public function tearDown(): void
    {
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