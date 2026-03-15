<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Features\CheckEndIf\CheckEndIfSyntax;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckEndIfTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Color::$color = false;
        Console::enforceTrue();
        Console::recoredWrites();
        ErrorPrinter::$instance = null;
        CheckEndIfSyntax::$cache = false;
        copy(__DIR__.'/EndifStubs/endif-init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        Console::reset();
        CheckEndIfSyntax::$cache = true;
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test()
    {
        $this->artisan('check:endif')->assertSuccessful()->run();

        $this->assertEquals([
            'Do you have committed everything in git?',
            'Replacing endif in: app'.DIRECTORY_SEPARATOR.'endIf.php',
        ], Console::$askedConfirmations);

        $this->assertFileEquals(
            __DIR__.'/EndifStubs/endif-expected.stub',
            $this->tmpFileUnderTest()
        );
    }

    private function tmpFileUnderTest(): string
    {
        return app_path('endIf.php');
    }
}
