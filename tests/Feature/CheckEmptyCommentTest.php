<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Reports\ComposerJsonReport;

class CheckEmptyCommentTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        ComposerJsonReport::$callback = null;

        copy(__DIR__.'/CheckEmptyCommentStubs/init.stub', $this->mainPath());
    }

    public function tearDown(): void
    {
        @unlink($this->mainPath());
        ComposerJsonReport::$callback = null;
        parent::tearDown();
    }

    public function test()
    {
        $r = $this->artisan('check:empty_comments')
            ->expectsQuestion('Do you want to replace empty_comment.php with new version of it?', 'yes')
            ->run();

        $this->assertEquals(0, $r);
        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckEmptyCommentStubs/expected.stub'),
            file_get_contents($this->mainPath())
        );
    }

    private function mainPath()
    {
        return app_path('empty_comment.php');
    }
}