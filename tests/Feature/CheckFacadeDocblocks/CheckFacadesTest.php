<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckFacadesTest extends TestCase
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
        @unlink(app_path('SampleFacade.php'));
        @unlink(app_path('MySampleRoot.php'));
        @unlink(app_path('NoRootFacade.php'));
        parent::tearDown();
    }

    public function test_1()
    {
        copy(__DIR__.'/CheckFacadeDocblocksStubs/SampleFacade.stub', app_path('SampleFacade.php'));
        copy(__DIR__.'/CheckFacadeDocblocksStubs/MySampleRoot.stub', app_path('MySampleRoot.php'));

        $hasError = $this->artisan('check:facades')->assertFailed()->run();

        $this->assertFileEquals(
            __DIR__.'/CheckFacadeDocblocksStubs/SampleFacade-result.stub',
            app_path('SampleFacade.php')
        );

        $write = (Console::$instance)->writeln;
        unset($write[4]);
        $this->assertEquals([
            0 => '   1 App\SampleFacade',
            1 => '    ➖ Fixed doc-blocks for:',
            2 => 'at app'.DIRECTORY_SEPARATOR.'SampleFacade.php:4',
            3 => '_______',
        ], $write);

        $this->assertEquals(1, $hasError);
    }

    public function test_2()
    {
        copy(__DIR__.'/CheckFacadeDocblocksStubs/NoRootFacade.stub', app_path('NoRootFacade.php'));

        $this->artisan('check:facades')->assertFailed()->run();

        $write = (Console::$instance)->writeln;
        unset($write[4]);
        $this->assertEquals([
            0 => '   1 The Facade Accessor Not Found.',
            1 => '   "NoRoot"',
            2 => 'at app'.DIRECTORY_SEPARATOR.'NoRootFacade.php:20',
            3 => '_______',
        ], $write);

        $this->assertFileEquals(
            __DIR__.'/CheckFacadeDocblocksStubs/NoRootFacade.stub',
            app_path('NoRootFacade.php')
        );
    }
}
