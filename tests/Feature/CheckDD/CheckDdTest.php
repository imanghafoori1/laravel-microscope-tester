<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Features\CheckDD\CheckDD;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;
use Imanghafoori\LaravelMicroscope\SpyClasses\RoutePaths;

class CheckDdTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Color::$color = CheckDD::$cache = false;
        Console::recoredWrites();
        ErrorPrinter::$terminalWidth = 10;
        RoutePaths::$additionalFiles = [base_path('routes/web2.php')];

        copy(__DIR__.'/CheckDDStubs/init.stub', $this->tmpFileUnderTest());
        copy(__DIR__.'/CheckDDStubs/sample_route.stub', base_path('routes/web2.php'));
    }

    public function tearDown(): void
    {
        Color::$color = CheckDD::$cache = true;
        Console::reset();
        ErrorPrinter::$instance = null;
        RoutePaths::$additionalFiles = [];

        @unlink(base_path('routes/web2.php'));
        @unlink($this->tmpFileUnderTest());

        parent::tearDown();
    }

    public function test()
    {
        $this->artisan('check:dd')->assertFailed()->run();

        $write = (Console::$instance)->writeln;
        array_pop($write);
        $ds = DIRECTORY_SEPARATOR;
        $this->assertEquals([
            "   1 Debug function found: 'dump'",
            "   4| dump('sss'...",
            "at routes{$ds}web2.php:4",
            "_______",
            "   2 Debug function found: 'DD'",
            "   8| DD('sfvsf'...",
            "at app{$ds}dd.php:8",
            "_______",
            "   3 Debug function found: 'dd'",
            "   9| dd('sfvsf'...",
            "at app{$ds}dd.php:9",
            "_______",
        ], $write);
    }

    private function tmpFileUnderTest()
    {
        return app_path('dd.php');
    }
}
