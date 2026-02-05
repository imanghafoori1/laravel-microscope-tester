<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Color;

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
        Color::$color = true;
        @unlink($this->tmpFileUnderTest());
        parent::tearDown();
    }

    public function test()
    {
        $r = $this->artisan('enforce:helper_functions')
            ->expectsQuestion('Do you want to replace Helper.php with new version of it?', true)
            ->run();

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
