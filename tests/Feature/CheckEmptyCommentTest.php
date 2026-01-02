<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Color;

class CheckEmptyCommentTest extends TestCase
{
    public function setUp(): void
    {
        Color::$color = false;
        parent::setUp();
    }

    public function tearDown(): void
    {
        Color::$color = true;
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test()
    {
        copy(__DIR__.'/CheckEmptyCommentStubs/init.stub', $this->tmpFileUnderTest());

        $r = $this->artisan('check:empty_comments')
            ->expectsQuestion('Do you want to replace empty_comment.php with new version of it?', 'yes')
            ->run();

        $this->assertEquals(1, $r);
        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckEmptyCommentStubs/expected.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );
    }

    public function test_2()
    {
        copy(__DIR__.'/CheckEmptyCommentStubs/init.stub', $this->tmpFileUnderTest());

        $r = $this->artisan('check:empty_comments')
            ->expectsQuestion('Do you want to replace empty_comment.php with new version of it?', false)
            ->run();

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
