<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Reports\ComposerJsonReport;

class CheckBadPracticesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        @unlink($this->mainPath());
        ComposerJsonReport::$callback = null;
        copy(__DIR__.'/CheckEnvStubs/init.stub', $this->mainPath());
    }

    public function tearDown(): void
    {
        ComposerJsonReport::$callback = null;
        @unlink($this->mainPath());
        parent::tearDown();
    }

    public function test()
    {
        $r = $this->artisan('check:bad_practices')
            ->expectsOutputToContain('Checking for env() calls outside config files...')
            ->expectsOutputToContain('env() function found:')
            ->expectsOutputToContain('env')
            ->expectsOutputToContain('app'.DIRECTORY_SEPARATOR.'Env.php:5')
            ->assertExitCode(1)
            ->run();

        $this->assertEquals(1, $r);
    }

    private function mainPath()
    {
        return app_path('Env.php');
    }
}