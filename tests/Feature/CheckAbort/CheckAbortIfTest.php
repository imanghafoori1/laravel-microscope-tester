<?php

use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\File;
use Imanghafoori\LaravelMicroscope\Foundations\Color;

class CheckAbortIfTest extends TestCase
{
    public function tearDown(): void
    {
        Color::$color = true;
        File::deleteDirectory($this->cachePath(), true);
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test()
    {
        Color::$color = false;
        copy(__DIR__.'/CheckAbortIfStubs/init.stub', $this->tmpFileUnderTest());

        $question = 'Do you want to replace abort_if.php with new version of it?';
        $r = $this->artisan('check:abort_if')->expectsQuestion($question, true)->run();

        $this->assertEquals(1, 1);
        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckAbortIfStubs/expected.stub'),
            file_get_contents($this->tmpFileUnderTest())
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
