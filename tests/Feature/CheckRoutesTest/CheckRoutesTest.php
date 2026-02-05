<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Features\SearchReplace\CachedFiles;
use Imanghafoori\LaravelMicroscope\Foundations\Color;

class CheckRoutesTest extends TestCase
{
    public function setUp(): void
    {
        Color::$color = false;
        $_SERVER['argv_original_1'] = $_SERVER['argv'][1];
        $_SERVER['argv'][1] = 'check:routes';
        parent::setUp();
        copy(__DIR__.'/CheckRoutesStub/init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        $_SERVER['argv'][1] = $_SERVER['argv_original_1'];
        Color::$color = true;
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test()
    {
        Route::get('/w', 'App\Http\Controllers\HomeController@index');
        Route::get('/w2', 'App\ABC@index');
        Route::get('/w3', 'App\ABC@F');
        Route::get('/w3', 'App\ABC@F');
        Route::get('/w4', 'App\ABC@F')->name('a2');
        Route::get('/w4', 'App\ABC@F')->name('a1');
        Route::group(['namespace' => 'a', 'middlewares' => 'a'], function () {

        });
        $ds = DIRECTORY_SEPARATOR;
        $exitCode = $this->artisan('check:routes')
            ->expectsOutputToContain('The controller can not be resolved: (url: "w")')
            ->expectsOutputToContain('App\Http\Controllers\HomeController')
            ->expectsOutputToContain('app'.$ds.'ABC.php')
            ->expectsOutputToContain('Route name does not exist:')
            ->expectsOutputToContain('1 route(...) calls were checked. (1 skipped)')
            ->expectsOutputToContain("route('sss')  <=== is wrong")
            ->expectsOutputToContain('App\ABC')
            ->expectsOutputToContain('Absent method for route url: "w2"')
            ->expectsOutputToContain('is overridden by an other route with same uri.')
            ->expectsOutputToContain("2 Incorrect 'middlewares' key.")
            ->expectsOutputToContain('Route with uri: GET,HEAD: /w4 is overridden.')
            ->expectsOutputToContain("['middlewares' => ...] key passed to Route::group(...) is not correct.")
            ->expectsOutputToContain("at tests{$ds}Feature{$ds}CheckRoutesTest{$ds}CheckRoutesTest.php:34")
            ->expectsOutputToContain("at tests{$ds}Feature{$ds}CheckRoutesTest{$ds}CheckRoutesTest.php:29")
            ->run();

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
