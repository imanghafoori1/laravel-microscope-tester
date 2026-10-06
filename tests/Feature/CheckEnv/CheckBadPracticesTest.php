<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckBadPracticesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Color::$color = false;
        Console::recoredWrites();
        ErrorPrinter::$instance = null;
        ErrorPrinter::$terminalWidth = 10;
    }

    public function tearDown(): void
    {
        @unlink($this->cacheFile());
        @unlink(app_path('Models/Env1.php'));
        @unlink(app_path('MyEnv2.php'));
        @unlink(app_path('EnvConfig1.php'));
        parent::tearDown();
    }

    public function test_0()
    {
        copy(__DIR__.'/CheckEnvStubs/init.stub', app_path('Models/Env1.php'));
        copy(__DIR__.'/CheckEnvStubs/namespaced.stub', app_path('MyEnv2.php'));
        copy(__DIR__.'/CheckEnvStubs/config.stub', app_path('EnvConfig1.php'));

        $this->artisan('check:bad_practices')->assertFailed()->run();

        $writeln = Console::$instance->writeln;
        array_pop($writeln);
        $ds = DIRECTORY_SEPARATOR;

        $this->assertTrue(in_array('   1 env() function found: ', $writeln));
        $this->assertTrue(in_array('   9| env(\'d\');', $writeln));
        $this->assertTrue(in_array('at app/MyEnv2.php:9', $writeln));
        $this->assertTrue(in_array('   2 env() function found: ', $writeln));
        $this->assertTrue(in_array('   5| env(\'s\');', $writeln));
        $this->assertTrue(in_array('at app/Models/Env1.php:5', $writeln));
        $this->assertTrue(in_array('   3 env() function found: ', $writeln));
        $this->assertTrue(in_array('   6| ENV(\'s\');', $writeln));
        $this->assertTrue(in_array('at app/Models/Env1.php:6', $writeln));
        $this->assertTrue(in_array('_______', $writeln));

        $this->assertFileExists($this->cacheFile());

        $array = require $this->cacheFile();
        $this->assertIsArray($array);
        $this->assertTrue(in_array('EnvConfig1.php', $array));
        $this->assertFalse(in_array('Env1.php', $array));
        $this->assertFalse(in_array('MyEnv2.php', $array));
    }

    private function cacheFile(): string
    {
        return storage_path('framework/cache/microscope/env_calls_command.php');
    }
}
