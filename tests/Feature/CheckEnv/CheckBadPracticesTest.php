<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Color;

class CheckBadPracticesTest extends TestCase
{
    public function setUp(): void
    {
        Color::$color = false;
        parent::setUp();
    }

    public function tearDown(): void
    {
        Color::$color = true;
        @unlink(storage_path('framework/cache/microscope/env_calls_command.php'));
        @unlink(app_path('Env1.php'));
        @unlink(app_path('Env2.php'));
        @unlink(app_path('EnvConfig1.php'));
        parent::tearDown();
    }

    public function test()
    {
        copy(__DIR__.'/CheckEnvStubs/init.stub', app_path('Env1.php'));
        copy(__DIR__.'/CheckEnvStubs/namespaced.stub', app_path('Env2.php'));
        copy(__DIR__.'/CheckEnvStubs/config.stub', app_path('EnvConfig1.php'));

        $r = $this->artisan('check:bad_practices')
            ->expectsOutputToContain('Checking for env() calls outside config files...')
            ->expectsOutputToContain('env() function found:')
            ->expectsOutputToContain('env')
            ->expectsOutputToContain('app'.DIRECTORY_SEPARATOR.'Env1.php:5')
            ->expectsOutputToContain('app'.DIRECTORY_SEPARATOR.'Env1.php:6')
            ->assertExitCode(1)
            ->run();

        $this->assertEquals(1, $r);
    }
}
