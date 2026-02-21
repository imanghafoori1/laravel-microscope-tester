<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Features\CheckEndIf\CheckEndIfSyntax;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckEndIfTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Color::$color = CheckEndIfSyntax::$cache = false;
        copy(__DIR__.'/EndifStubs/endif-init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        Console::reset();
        Color::$color = CheckEndIfSyntax::$cache = true;
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test()
    {
        Console::enforceTrue();
        $r = $this->artisan('check:endif')->run();

        $this->assertEquals([
            'Do you have committed everything in git?',
            'Replacing endif in: app'.DIRECTORY_SEPARATOR.'endIf.php',
        ], Console::$askedConfirmations);

        $this->assertEquals(0, $r);
        $this->assertEquals(
            file_get_contents(__DIR__.'/EndifStubs/endif-expected.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );
    }

    private function tmpFileUnderTest(): string
    {
        return app_path('endIf.php');
    }
}
