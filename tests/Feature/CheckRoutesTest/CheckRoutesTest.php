<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Features\SearchReplace\CachedFiles;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckRoutesTest extends TestCase
{
    public function setUp(): void
    {
        Color::$color = false;
        $_SERVER['argv_original_1'] = $_SERVER['argv'][1];
        $_SERVER['argv'][1] = 'check:routes';
        parent::setUp();
        Console::recoredWrites();
        ErrorPrinter::$instance = null;
        ErrorPrinter::$terminalWidth = 10;
        copy(__DIR__.'/CheckRoutesStub/init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        // clean up:
        $_SERVER['argv'][1] = $_SERVER['argv_original_1'];
        ErrorPrinter::$instance = null;
        Console::reset();
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test()
    {
        // define sample routes:
        Route::get('/w', 'App\Http\Controllers\HomeController@index');
        Route::get('/w2', 'App\ABC@index');
        Route::get('/w3', 'App\ABC@F');
        Route::get('/w3', 'App\ABC@F');
        Route::get('/w4', 'App\ABC@F')->name('a2');
        Route::get('/w4', 'App\ABC@F')->name('a1');
        Route::group(['namespace' => 'a', 'middlewares' => 'a'], function () {
        });
        $ds = DIRECTORY_SEPARATOR;
        $exitCode = $this->artisan('check:routes')->run();

        $write = (Console::$instance)->writeln;
        array_pop($write);

        $basePath = base_path("tests{$ds}Feature{$ds}CheckRoutesTest{$ds}CheckRoutesTest.php");
        $this->assertEquals([
            0 => "   1 Route with uri: GET,HEAD: /w4 is overridden.",
            1 => "   Route name: a2
 at {$basePath}:40
 is overridden by an other route with same uri.
 at {$basePath}:41
",
            2 => "at :4",
            3 => "_______",
            4 => "   2 Incorrect 'middlewares' key.",
            5 => "   ['middlewares' => ...] key passed to Route::group(...) is not correct.",
            6 => "at tests{$ds}Feature{$ds}CheckRoutesTest{$ds}CheckRoutesTest.php:42",
            7 => "_______",
            8 => "   3 The controller can not be resolved: (url: \"w\")",
            9 => "   App\Http\Controllers\HomeController",
            10 => "at tests{$ds}Feature{$ds}CheckRoutesTest{$ds}CheckRoutesTest.php:36",
            11 => "_______",
            12 => "   4 Absent method for route url: \"w2\"",
            13 => "   App\ABC@index",
            14 => "at tests{$ds}Feature{$ds}CheckRoutesTest{$ds}CheckRoutesTest.php:37",
            15 => "_______",
            16 => "   5 Route name does not exist: ",
            17 => "   route('sss')  <=== is wrong",
            18 => "at app{$ds}ABC.php:9",
            19 => "_______",
        ], $write);

        $this->assertEquals(1, $exitCode);
        $cache = $this->getCacheFilePath();
        $this->assertFileExists($cache);
        @unlink($cache);
    }

    private function tmpFileUnderTest()
    {
        return app_path('ABC.php');
    }

    private function getCacheFilePath(): string
    {
        return CachedFiles::getFolderPath().'check_route_calls.php';
    }
}
