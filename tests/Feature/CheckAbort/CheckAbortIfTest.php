<?php

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\File;
use Imanghafoori\LaravelMicroscope\ErrorReporters\ErrorPrinter;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckAbortIfTest extends TestCase
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
        Console::reset();
        File::deleteDirectory($this->cachePath(), true);
        @unlink($this->tmpFileUnderTest());
        ErrorPrinter::$instance = null;
        parent::tearDown();
    }

    public function test()
    {
        copy(__DIR__.'/CheckAbortIfStubs/init.stub', $this->tmpFileUnderTest());

        $question = 'Do you want to replace abort_if.php with new version of it?';
        Console::fakeAnswer($question);

        $this->artisan('check:abort_if')->assertFailed()->run();

        $this->assertFileEquals(
            __DIR__.'/CheckAbortIfStubs/expected.stub',
            $this->tmpFileUnderTest()
        );

        $cacheFile = $this->cachePath().'abort_if-code-v1.php';
        $this->assertFileExists($cacheFile);
        $cacheHashToFilename = require $cacheFile;
        $this->assertTrue(in_array('a.php', $cacheHashToFilename));
        $this->assertTrue(in_array('helpers.php', $cacheHashToFilename));
        $this->assertTrue(in_array('web.php', $cacheHashToFilename));
        $this->assertFalse(in_array('abort_if.php', $cacheHashToFilename));
        $this->assertTrue(in_array('User.php', $cacheHashToFilename));
        $this->assertTrue(in_array('UserFactory.php', $cacheHashToFilename));

        $cacheFile = $this->cachePath().'abort_if-code-v2.php';
        $this->assertFileExists($cacheFile);
        $cacheHashToFilename = require $cacheFile;
        $this->assertTrue(in_array('a.php', $cacheHashToFilename));
        $this->assertTrue(in_array('helpers.php', $cacheHashToFilename));
        $this->assertTrue(in_array('web.php', $cacheHashToFilename));
        $this->assertFalse(in_array('abort_if.php', $cacheHashToFilename));
        $this->assertTrue(in_array('User.php', $cacheHashToFilename));
        $this->assertTrue(in_array('UserFactory.php', $cacheHashToFilename));
    }

    private function tmpFileUnderTest()
    {
        return app_path('abort_if.php');
    }

    private function cachePath()
    {
        return storage_path('framework/cache/microscope/');
    }
}
