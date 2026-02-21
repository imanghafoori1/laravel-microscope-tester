<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckEmptyCommentTest extends TestCase
{
    public function setUp(): void
    {
        Color::$color = false;
        parent::setUp();
    }

    public function tearDown(): void
    {
        Console::reset();
        Color::$color = true;
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test_1()
    {
        copy(__DIR__.'/CheckEmptyCommentStubs/init.stub', $this->tmpFileUnderTest());
        Console::enforceTrue();

        $r = $this->artisan('check:empty_comments')->run();

        $this->assertEquals(
            [
                'Do you want to replace empty_comment.php with new version of it?',
                'Do you want to replace empty_comment.php with new version of it?',
                'Do you want to replace empty_comment.php with new version of it?',
            ],
            Console::$askedConfirmations
        );

        $this->assertEquals(1, $r);
        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckEmptyCommentStubs/expected.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );
    }

    public function test_2()
    {
        copy(__DIR__.'/CheckEmptyCommentStubs/init.stub', $this->tmpFileUnderTest());

        Console::$forcedAnswer = false;

        $r = $this->artisan('check:empty_comments')->run();

        $this->assertEquals([
            'Do you want to replace empty_comment.php with new version of it?',
            'Do you want to replace empty_comment.php with new version of it?',
            'Do you want to replace empty_comment.php with new version of it?',
        ] , Console::$askedConfirmations);

        $this->assertEquals(1, $r);
        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckEmptyCommentStubs/init.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );
    }

    private function tmpFileUnderTest()
    {
        return app_path('empty_comment.php');
    }
}
