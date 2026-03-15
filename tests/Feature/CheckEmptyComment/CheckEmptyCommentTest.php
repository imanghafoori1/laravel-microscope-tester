<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckEmptyCommentTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Color::$color = false;
        Console::recoredWrites();
    }

    public function tearDown(): void
    {
        Console::reset();
        @unlink($this->tmpFileUnderTest());

        parent::tearDown();
    }

    public function test_1()
    {
        copy(__DIR__.'/CheckEmptyCommentStubs/init.stub', $this->tmpFileUnderTest());
        Console::enforceTrue();

        $this->artisan('check:empty_comments')->assertFailed()->run();

        $this->assertEquals(
            [
                'Do you want to replace empty_comment.php with new version of it?',
                'Do you want to replace empty_comment.php with new version of it?',
                'Do you want to replace empty_comment.php with new version of it?',
            ],
            Console::$askedConfirmations
        );

        $writeln = Console::$instance->writeln;
        array_pop($writeln);

        $ds = DIRECTORY_SEPARATOR;
        $eol = PHP_EOL;
        $this->assertEquals([
            "Replacing:$eol//",
            "With:$eol",
            "Replacement will occur at:",
            "at app{$ds}empty_comment.php:5",
            "Replacing:$eol//",
            "With:$eol",
            "Replacement will occur at:",
            "at app{$ds}empty_comment.php:7",
            "Replacing:$eol//",
            "With:$eol",
            "Replacement will occur at:",
            "at app{$ds}empty_comment.php:9",
        ], $writeln);

        $this->assertFileEquals(
            __DIR__.'/CheckEmptyCommentStubs/expected.stub',
            $this->tmpFileUnderTest()
        );
    }

    public function test_2()
    {
        copy(__DIR__.'/CheckEmptyCommentStubs/init.stub', $this->tmpFileUnderTest());

        Console::$forcedAnswer = false;

        $this->artisan('check:empty_comments')->assertFailed()->run();

        $this->assertEquals([
            'Do you want to replace empty_comment.php with new version of it?',
            'Do you want to replace empty_comment.php with new version of it?',
            'Do you want to replace empty_comment.php with new version of it?',
        ] , Console::$askedConfirmations);

        $this->assertFileEquals(
            __DIR__.'/CheckEmptyCommentStubs/init.stub',
            $this->tmpFileUnderTest()
        );

        $cachePath = storage_path('framework/cache/microscope/delete_empty_comments-v1.php');
        $this->assertFileExists($cachePath);

        $content = require $cachePath;
        $this->assertIsArray($content);

        @unlink($cachePath);
    }

    private function tmpFileUnderTest()
    {
        return app_path('empty_comment.php');
    }
}
