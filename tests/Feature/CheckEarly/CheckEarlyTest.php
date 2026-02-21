<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckEarlyTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Color::$color = false;
        Console::$instance = new class
        {
            public $writeln = [];

            public function writeln($write)
            {
                $this->writeln[] = $write;
            }
        };
        copy(__DIR__.'/CheckEarlyStubs/init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        Console::reset();
        @unlink($this->tmpFileUnderTest());
        Color::$color = true;
        parent::tearDown();
    }

    public function test_0()
    {
        Console::enforceTrue();

        $r = $this->artisan('check:early_returns')
            ->expectsOutputToContain('Checking for Early Returns...')
            ->run();

        $writeln = Console::$instance->writeln;

        $this->assertEquals($writeln, [
            ' Warning! This command is going to make "CHANGES" to your files!',
            PHP_EOL.'1 fix applied to: early.php',
        ]);
        $this->assertEquals([
            ' Do you have committed everything in git?',
            ' Do you want to flatten: app'.DIRECTORY_SEPARATOR.'early.php',
        ], Console::$askedConfirmations);

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckEarlyStubs/expected.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );

        $this->assertEquals(0, $r);
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
        $r = $this->artisan('check:early_returns --nofix')->run();

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckEarlyStubs/init.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );

        $this->assertEquals(0, $r);
    }

    private function tmpFileUnderTest()
    {
        return app_path('early.php');
    }
}