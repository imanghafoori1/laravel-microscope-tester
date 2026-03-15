<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckGenericDocblocksTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Color::$color = false;
        Console::recoredWrites();
        copy(__DIR__.'/CheckGenericDocblocksStubs/init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        Console::reset();
        @unlink($this->tmpFileUnderTest());

        parent::tearDown();
    }

    public function test()
    {
        Console::enforceTrue();

        $this->artisan('check:generic_docblocks')
            ->expectsOutputToContain('7 generic doc-blocks were found.')
            ->assertFailed()
            ->run();

        $this->assertEquals([
            'Do you want to remove doc-blocks from: HelloController.php'
        ], Console::$askedConfirmations);

        $this->assertFileEquals(
            __DIR__.'/CheckGenericDocblocksStubs/expected.stub',
            $this->tmpFileUnderTest()
        );
    }

    private function tmpFileUnderTest()
    {
        return app_path('HelloController.php');
    }
}
