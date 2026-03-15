<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckBladeQueriesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Color::$color = false;
        Console::recoredWrites();
        ErrorPrinter::$instance = null;
        ErrorPrinter::$terminalWidth = 10;

        @mkdir(resource_path());
        @mkdir(resource_path('views'));
        copy(__DIR__.'/CheckBladeQueriesStubs/init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        Console::reset();
        ErrorPrinter::$instance = null;

        @unlink($this->tmpFileUnderTest());
        @rmdir(resource_path('views'));
        @rmdir(resource_path());

        parent::tearDown();
    }

    public function test()
    {
        $this->artisan('check:blade_queries')->assertFailed()->run();

        $ds = DIRECTORY_SEPARATOR;
        $write = (Console::$instance)->writeln;
        array_pop($write);

        $this->assertEquals([
            '   1 Query in blade file: ',
            '   \App\Models\User  <=== DB query in blade file',
            "at resources{$ds}views{$ds}blade_queries.blade.php:4",
            '_______',
            '   2 Query in blade file: ',
            '   \DB  <=== DB query in blade file',
            "at resources{$ds}views{$ds}blade_queries.blade.php:5",
            '_______',
            '   3 Query in blade file: ',
            '   DB  <=== DB query in blade file',
            "at resources{$ds}views{$ds}blade_queries.blade.php:6",
            '_______',
        ], $write);
    }

    private function tmpFileUnderTest()
    {
        return resource_path('views/blade_queries.blade.php');
    }
}
