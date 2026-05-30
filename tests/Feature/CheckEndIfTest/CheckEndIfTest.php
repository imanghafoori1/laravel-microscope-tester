<?php

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\File;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
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

        copy(__DIR__.'/EndifStubs/endif-init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        Console::reset();
        File::deleteDirectory($this->cachePath(), true);
        @unlink($this->tmpFileUnderTest());

        parent::tearDown();
    }

    public function test()
    {
        $this->artisan('check:endif')
            ->assertFailed()
            ->run();

        $this->assertEquals([
            'Do you have committed everything in git?',
            'Replacing endif in: app'.DIRECTORY_SEPARATOR.'endIf.php',
        ], Console::$askedConfirmations);

        $this->assertFileEquals(
            __DIR__.'/EndifStubs/endif-expected.stub',
            $this->tmpFileUnderTest()
        );

        $cache = require $this->cachePath().'check_ruby_syntax.php';
        $this->assertIsArray($cache);
        $this->assertTrue(in_array('User.php', $cache));
        $this->assertTrue(in_array('endIf.php', $cache));
        $this->assertTrue(in_array('a.php', $cache));
    }

    public function test_nofix()
    {
        ErrorPrinter::$terminalWidth = 10;

        $this->artisan('check:endif --nofix')
            ->assertFailed()
            ->run();

        $this->assertEquals([
            'Do you have committed everything in git?',
        ], Console::$askedConfirmations);

        $this->assertFileEquals(
            __DIR__.'/EndifStubs/endif-init.stub',
            $this->tmpFileUnderTest()
        );

        $write = (Console::$instance)->writeln;
        array_pop($write);
        $ds = DIRECTORY_SEPARATOR;
        $this->assertEquals([
            '   1 Ruby like syntax found:',
            '   ',
            "at app{$ds}endIf.php:4",
            '_______',
        ], $write);

        $cache = require $this->cachePath().'check_ruby_syntax.php';
        $this->assertIsArray($cache);
        $this->assertTrue(in_array('User.php', $cache));
        $this->assertTrue(! in_array('endIf.php', $cache));
        $this->assertTrue(in_array('a.php', $cache));
    }

    private function tmpFileUnderTest(): string
    {
        return app_path('endIf.php');
    }

    private function cachePath()
    {
        return storage_path('framework/cache/microscope/');
    }
}
