<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Foundations\Reports\ComposerJsonReport;

class CheckRoutesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        ComposerJsonReport::$callback = null;
        ErrorPrinter::$instance = null;
        copy(__DIR__.'/CheckRoutesStub/init.stub', $this->mainPath());
    }

    public function tearDown(): void
    {
        @unlink($this->mainPath());
        ComposerJsonReport::$callback = null;
        ErrorPrinter::$instance = null;
        parent::tearDown();
    }

    public function test()
    {
        Route::get('/w', 'App\Http\Controllers\HomeController@index');

        $r = $this->artisan('check:routes')
            ->expectsOutputToContain('The controller can not be resolved: (url: "w")')
            ->expectsOutputToContain('App\Http\Controllers\HomeController')
            ->expectsOutputToContain('app'.DIRECTORY_SEPARATOR.'ABC.php')
            ->expectsOutputToContain('route name does not exist:')
            ->expectsOutputToContain('\'sss\'')
            ->run();

        $this->assertEquals(1, $r);
    }

    private function mainPath()
    {
        return app_path('ABC.php');
    }
}