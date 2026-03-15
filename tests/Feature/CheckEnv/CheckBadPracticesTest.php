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
        @unlink(app_path('Env1.php'));
        @unlink(app_path('Env2.php'));
        @unlink(app_path('EnvConfig1.php'));
        parent::tearDown();
    }

    public function test_0()
    {
        copy(__DIR__.'/CheckEnvStubs/init.stub', app_path('Env1.php'));
        copy(__DIR__.'/CheckEnvStubs/namespaced.stub', app_path('Env2.php'));
        copy(__DIR__.'/CheckEnvStubs/config.stub', app_path('EnvConfig1.php'));

        $this->artisan('check:bad_practices')->assertFailed()->run();

        $writeln = Console::$instance->writeln;
        array_pop($writeln);
        $ds = DIRECTORY_SEPARATOR;
        $this->assertEquals([
            '   1 env() function found: ',
            "   5| env('s');",
            "at app{$ds}Env1.php:5",
            '_______',
            '   2 env() function found: ',
            "   6| ENV('s');",
            "at app{$ds}Env1.php:6",
            '_______',
            '   3 env() function found: ',
            "   9| env('d');",
            "at app{$ds}Env2.php:9",
            '_______',
        ], $writeln);

        $this->assertFileExists($this->cacheFile());

        $array = require $this->cacheFile();
        $this->assertIsArray($array);
        $this->assertTrue(in_array('EnvConfig1.php', $array));
        $this->assertFalse(in_array('Env1.php', $array));
        $this->assertFalse(in_array('Env2.php', $array));
    }

    private function cacheFile(): string
    {
        return storage_path('framework/cache/microscope/env_calls_command.php');
    }
}
