<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;

class CheckFacadesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        ErrorPrinter::$instance = null;
        copy(__DIR__.'/CheckFacadeDocblocksStubs/SampleFacade.stub', app_path('SampleFacade.php'));
        copy(__DIR__.'/CheckFacadeDocblocksStubs/MySampleRoot.stub', app_path('MySampleRoot.php'));
    }

    public function tearDown(): void
    {
        unlink(app_path('SampleFacade.php'));
        unlink(app_path('MySampleRoot.php'));
        ErrorPrinter::$instance = null;
        parent::tearDown();
    }

    public function test()
    {
        $r = $this->artisan('check:facades')->run();
        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckFacadeDocblocksStubs/SampleFacade-result.stub'),
            file_get_contents(app_path('SampleFacade.php'))
        );

        $this->assertEquals(0, $r);
    }
}