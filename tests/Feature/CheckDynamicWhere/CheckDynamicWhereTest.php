<?php

use Illuminate\Foundation\Testing\TestCase;
use Imanghafoori\LaravelMicroscope\Foundations\Color;
use Imanghafoori\LaravelMicroscope\Foundations\Console;

class CheckDynamicWhereTest extends TestCase
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

    public function test()
    {
        copy(__DIR__.'/DynamicWhereStubs/dynamic-where-init.stub', $this->tmpFileUnderTest());

        Console::enforceTrue();
        $r = $this->artisan('check:dynamic_wheres')->run();

        $this->assertEquals([
            'Do you want to replace dynamic_wheres.php with new version of it?',
            'Do you want to replace dynamic_wheres.php with new version of it?',
        ], Console::$askedConfirmations);

        $this->assertEquals(1, $r);

        $this->assertEquals(
            file_get_contents(__DIR__.'/DynamicWhereStubs/dynamic-where-final.stub'),
            file_get_contents($this->tmpFileUnderTest())
        );
    }

    private function tmpFileUnderTest()
    {
        return app_path('dynamic_wheres.php');
    }
}