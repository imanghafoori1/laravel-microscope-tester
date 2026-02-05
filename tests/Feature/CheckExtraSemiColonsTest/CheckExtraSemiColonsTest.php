<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Color;

class CheckExtraSemiColonsTest extends TestCase
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
        copy(__DIR__.'/CheckExtraSemiColonStubs/init.stub', $this->tmpFileUnderTest());

        $r = $this->artisan('check:extra_semi_colons')
            ->expectsQuestion('Do you want to replace extra_semi_colons.php with new version of it?', 'yes')
            ->run();

        $this->assertEquals(1, $r);

        $this->assertEquals(
            file_get_contents(__DIR__.'/CheckExtraSemiColonStubs/expected.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );
    }

    private function tmpFileUnderTest()
    {
        return app_path('extra_semi_colons.php');
    }
}
