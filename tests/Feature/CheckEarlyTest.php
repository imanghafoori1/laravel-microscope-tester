<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Color;

class CheckEarlyTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Color::$color = false;
        copy(__DIR__.'/CheckEarlyStubs/init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        @unlink($this->tmpFileUnderTest());
        Color::$color = true;
        parent::tearDown();
    }

    public function test_0()
    {
        $r = $this->artisan('check:early_returns')
            ->expectsQuestion(' Do you have committed everything in git?', true)
            ->expectsQuestion(' Do you want to flatten: app'.DIRECTORY_SEPARATOR.'early.php', true)
            ->run();

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckEarlyStubs/expected.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );

        $this->assertEquals(0, $r);
    }

    public function test_1()
    {
        $r = $this->artisan('check:early_returns')
            ->expectsQuestion(' Do you have committed everything in git?', true)
            ->expectsQuestion(' Do you want to flatten: app'.DIRECTORY_SEPARATOR.'early.php', false)
            ->run();

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