<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class EnforceHelpersTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Color::$color = false;
        copy(__DIR__.'/EnforceHelpers/init.stub', $this->tmpFileUnderTest());
    }

    public function tearDown(): void
    {
        Console::reset();
        Color::$color = true;
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test()
    {
        Console::enforceTrue();

        $r = $this->artisan('enforce:helper_functions')->run();
        $this->assertEquals([
            'Do you want to replace Helper.php with new version of it?',
            'Do you want to replace Helper.php with new version of it?',
            'Do you want to replace Helper.php with new version of it?',
            'Do you want to replace Helper.php with new version of it?',
        ], Console::$askedConfirmations);
        $this->assertEquals(1, $r);

        $this->assertEquals(
            file_get_contents(__DIR__.'/EnforceHelpers/expected.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );
    }

    private function tmpFileUnderTest(): string
    {
        return app_path('Helper.php');
    }
}
