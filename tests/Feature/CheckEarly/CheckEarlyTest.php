<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckEarlyTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Color::$color = false;
        ErrorPrinter::$terminalWidth = 10;
        Console::recoredWrites();
        copy(__DIR__.'/CheckEarlyStubs/init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        Console::reset();
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test_0()
    {
        Console::enforceTrue();

        $this->artisan('check:early_returns')
            ->expectsOutputToContain('Checking for Early Returns...')
            ->assertExitCode(1)
            ->run();

        $writeln = Console::$instance->writeln;
        array_pop($writeln);

        $this->assertEquals([
            ' Warning! This command is going to make "CHANGES" to your files!',
            //'   1 fix applied to: early.php',
            1 => '   1 code was refactored',
            2 => '   '.PHP_EOL.'1 fix applied to: early.php',
            3 => 'at app'.DIRECTORY_SEPARATOR.'early.php:1',
            4 => '_______',
        ], $writeln);
        $this->assertEquals([
            ' Do you have committed everything in git?',
            ' Do you want to flatten: app'.DIRECTORY_SEPARATOR.'early.php',
        ], Console::$askedConfirmations);

        $this->assertFileEquals(
            __DIR__.'/CheckEarlyStubs/expected.stub',
            $this->tmpFileUnderTest()
        );
    }

    public function test_1()
    {
        Console::fakeAnswer(' Do you have committed everything in git?');
        Console::fakeAnswer(' Do you want to flatten: app'.DIRECTORY_SEPARATOR.'early.php', false);

        $r = $this->artisan('check:early_returns')->run();

        $this->assertEquals([
            ' Do you have committed everything in git?',
            ' Do you want to flatten: app'.DIRECTORY_SEPARATOR.'early.php',
        ], Console::$askedConfirmations);

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckEarlyStubs/init.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );

        $this->assertEquals(0, $r);
    }

    public function test_no_fix()
    {
        $this->artisan('check:early_returns --nofix')->assertFailed()->run();

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckEarlyStubs/init.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );
    }

    private function tmpFileUnderTest()
    {
        return app_path('early.php');
    }
}