<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class EnforceHelpersTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Color::$color = false;
        Console::recoredWrites();
        ErrorPrinter::$instance = null;
        ErrorPrinter::$terminalWidth = 10;
        copy(__DIR__.'/EnforceHelpers/init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        Console::reset();
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test()
    {
        Console::enforceTrue();

        $this->artisan('enforce:helper_functions')->assertFailed()->run();
        $this->assertEquals([
            'Do you want to replace Helper.php with new version of it?',
            'Do you want to replace Helper.php with new version of it?',
            'Do you want to replace Helper.php with new version of it?',
            'Do you want to replace Helper.php with new version of it?',
        ], Console::$askedConfirmations);

        $write = (Console::$instance)->writeln;
        array_pop($write);
        $ds = DIRECTORY_SEPARATOR;
        $this->assertEquals([
            'Replacing:'.PHP_EOL.'Config::',
            'With:'.PHP_EOL.'config()->',
            'Replacement will occur at:',
            'at app'.$ds.'Helper.php:12',
            'Replacing:'.PHP_EOL.'\Cache::',
            'With:'.PHP_EOL.'cache()->',
            'Replacement will occur at:',
            'at app'.$ds.'Helper.php:13',
            'Replacing:'.PHP_EOL.'Auth::',
            'With:'.PHP_EOL.'auth()->',
            'Replacement will occur at:',
            'at app'.$ds.'Helper.php:14',
            'Replacing:'.PHP_EOL.'\Illuminate\Support\Facades\Session::',
            'With:'.PHP_EOL.'session()->',
            'Replacement will occur at:',
            'at app'.$ds.'Helper.php:15',
        ], $write);

        $this->assertFileEquals(
            __DIR__.'/EnforceHelpers/expected.stub',
            $this->tmpFileUnderTest()
        );
    }

    private function tmpFileUnderTest(): string
    {
        return app_path('Helper.php');
    }
}
