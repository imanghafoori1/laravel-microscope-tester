<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckArrowFunctionTest extends TestCase
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

    public function test()
    {
        copy(__DIR__.'/CheckArrowFunctionStub/init.stub', $this->tmpFileUnderTest());

        Console::enforceTrue();

        $this->artisan('check:arrow_functions')->assertFailed()->run();

        $this->assertEquals([
            'Do you want to replace arrow.php with new version of it?',
            'Do you want to replace arrow.php with new version of it?',
            'Do you want to replace arrow.php with new version of it?',
        ], Console::$askedConfirmations);

        $this->assertFileEquals(
            __DIR__.'/CheckArrowFunctionStub/expected.stub',
            $this->tmpFileUnderTest()
        );
    }

    private function tmpFileUnderTest()
    {
        return app_path('arrow.php');
    }
}
